<?php
//InventoryController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vaccine;
use App\Models\VaccineInventory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    /**
     * Check if current health worker is RHU admin (can modify inventory).
     * Returns error response if not authorized.
     */
    private function checkRHUAccess()
    {
        $healthWorker = $this->getHealthWorker();
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            return response()->json([
                'success' => false,
                'message' => 'Only RHU administrators can modify inventory.'
            ], 403);
        }
        
        return null; // No error, access granted
    }

    // Method to display the inventory form
    public function index()
    {
        $healthWorker = $this->getHealthWorker();
        
        // Clear API cache to ensure fresh data
        Cache::forget('vaccine_stocks_list');
        
        // Force fresh database connection to bypass any connection-level caching
        DB::reconnect();
        
        // Pull all vaccines ordered by ID (database sequence)
        $vaccines = Vaccine::orderBy('id')->get();

        $inventorySummary = $vaccines->map(function ($vaccine) {
            $availableDoses = max(0, (int) ($vaccine->stocks ?? 0));
            $availableBottles = max(0, (int) ($vaccine->available_bottles ?? 0));
            $dosesPerBottle = max(1, (int) ($vaccine->doses_per_bottle ?? 10));

            if ($availableDoses <= 0) {
                $status = 'out';
            } elseif ($availableDoses < 10) {
                $status = 'low';
            } elseif ($availableDoses < 50) {
                $status = 'medium';
            } else {
                $status = 'high';
            }

            return [
                'vaccine' => $vaccine->fresh(),
                'available_doses' => $availableDoses,
                'available_bottles' => $availableBottles,
                'doses_per_bottle' => $dosesPerBottle,
                'status' => $status,
            ];
        });

        // Prevent ALL caching - force fresh data every time
        return response()
            ->view('health_worker.inventory', compact('inventorySummary', 'healthWorker'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->header('ETag', '')
            ->header('Vary', '*');
    }

    // Method to show inventory batches for a specific vaccine
    public function showInventory($vaccineId)
    {
        try {
            $vaccine = Vaccine::findOrFail($vaccineId);
            
            // Get all inventory batches for this vaccine, ordered by FIFO
            $inventories = VaccineInventory::where('vaccine_id', $vaccineId)
                ->orderBy('received_date', 'asc')
                ->get()
                ->map(function($inv) {
                    return [
                        'id' => $inv->id,
                        'doses_per_bottle' => $inv->doses_per_bottle,
                        'bottles_total' => $inv->bottles_total,
                        'bottles_used' => $inv->bottles_used,
                        'doses_used' => $inv->doses_used,
                        'total_doses' => $inv->total_doses,
                        'available_doses' => $inv->available_doses,
                        'available_bottles' => $inv->available_bottles,
                        'received_date' => $inv->received_date,
                        'notes' => $inv->notes,
                        'status' => $inv->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'vaccine' => [
                    'id' => $vaccine->id,
                    'name' => $vaccine->vaccine_name,
                    'description' => $vaccine->doses_description,
                ],
                'inventories' => $inventories,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to add new stock batch (FIFO approach - create new record)
    // RHU ONLY - Barangay workers cannot add stock
    public function addStock(Request $request, $vaccineId)
    {
        // Check RHU access
        $accessError = $this->checkRHUAccess();
        if ($accessError) return $accessError;
        
        try {
            // Validate the request data
            $request->validate([
                'doses_per_bottle' => 'required|integer|min:1',
                'bottles_to_add' => 'required|integer|min:1',
                'received_date' => 'required|date',
                'notes' => 'nullable|string|max:500',
            ]);

            $vaccine = Vaccine::findOrFail($vaccineId);

            // Create new inventory batch (FIFO - don't modify existing batches)
            $inventory = VaccineInventory::create([
                'vaccine_id' => $vaccineId,
                'doses_per_bottle' => $request->doses_per_bottle,
                'bottles_total' => $request->bottles_to_add,
                'bottles_used' => 0,
                'doses_used' => 0,
                'received_date' => $request->received_date,
                'created_by' => Auth::guard('health_worker')->id(),
                'notes' => $request->notes,
            ]);

            // Clear the vaccine stocks cache
            Cache::forget('vaccine_stocks_list');

            return response()->json([
                'success' => true,
                'message' => 'Stock added successfully',
                'inventory' => [
                    'id' => $inventory->id,
                    'doses_per_bottle' => $inventory->doses_per_bottle,
                    'bottles_total' => $inventory->bottles_total,
                    'bottles_used' => $inventory->bottles_used,
                    'doses_used' => $inventory->doses_used,
                    'total_doses' => $inventory->total_doses,
                    'available_doses' => $inventory->available_doses,
                    'available_bottles' => $inventory->available_bottles,
                    'received_date' => $inventory->received_date,
                    'notes' => $inventory->notes,
                    'status' => $inventory->status,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stock: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method to store inventory data
    // RHU ONLY - Barangay workers cannot store inventory
    public function store(Request $request)
    {
        // Check RHU access
        $accessError = $this->checkRHUAccess();
        if ($accessError) return $accessError;
        
        // Validate the request data
        $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'stocks' => 'required|integer',
        ]);

        // Store the vaccine data
        Vaccine::create([
            'vaccine_name' => $request->vaccine_name,
            'stocks' => $request->stocks,
            'doses_description' => '', // Default empty description
        ]);

        // Clear the vaccine stocks cache
        Cache::forget('vaccine_stocks_list');

        // Redirect back to the inventory page with a success message
        return redirect()->route('inventory.index')->with('success', 'Vaccine added successfully.');
    }

    // Method to update inventory data
    // RHU ONLY - Barangay workers cannot update inventory
    public function update(Request $request, $id)
    {
        // Check RHU access
        $accessError = $this->checkRHUAccess();
        if ($accessError) return $accessError;
        
        try {
            $validated = $request->validate([
                'available_bottles' => 'required|integer|min:0',
                'doses_per_bottle' => 'required|integer|min:1',
                'available_doses' => 'nullable|integer|min:0',
            ]);

            $vaccine = Vaccine::findOrFail($id);
            $oldStocks = (int) $vaccine->stocks;

            $newBottles = (int) ($validated['available_bottles'] ?? 0);
            $dosesPerBottle = (int) ($validated['doses_per_bottle'] ?? 10);
            $manualDoses = array_key_exists('available_doses', $validated)
                ? ($validated['available_doses'] ?? null)
                : null;

            $calculatedDoses = $newBottles * $dosesPerBottle;
            $newStocks = is_null($manualDoses) ? $calculatedDoses : (int) $manualDoses;

            $vaccine->stocks = $newStocks;
            $vaccine->available_bottles = $newBottles;
            $vaccine->doses_per_bottle = $dosesPerBottle;
            
            // Reset the tracking counter when user manually updates inventory
            $vaccine->doses_used_from_current_bottles = 0;
            
            $vaccine->save();

            $availableDoses = (int) ($vaccine->stocks ?? 0);
            $availableBottles = (int) ($vaccine->available_bottles ?? 0);
            $currentDosesPerBottle = (int) ($vaccine->doses_per_bottle ?? 10);

            if ($availableDoses <= 0) {
                $status = 'out';
            } elseif ($availableDoses < 10) {
                $status = 'low';
            } elseif ($availableDoses < 50) {
                $status = 'medium';
            } else {
                $status = 'high';
            }

            // Clear all caches to ensure fresh data on reload
            Cache::forget('vaccine_stocks_list');
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'old_stock' => $oldStocks,
                'new_stock' => $newStocks,
                'available_doses' => $availableDoses,
                'available_bottles' => $availableBottles,
                'doses_per_bottle' => $currentDosesPerBottle,
                'status' => $status,
                'vaccine_id' => $vaccine->id,
                'vaccine_name' => $vaccine->vaccine_name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Method to add a new vaccine to inventory
    // RHU ONLY - Barangay workers cannot add vaccines
    public function addVaccine(Request $request)
    {
        // Check RHU access
        $accessError = $this->checkRHUAccess();
        if ($accessError) return $accessError;
        
        try {
            // Validate the request data
            $request->validate([
                'vaccine_name' => 'required|string|max:255|unique:vaccines,vaccine_name',
                'description' => 'nullable|string|max:500',
                'stocks' => 'required|integer|min:0'
            ]);

            // Create the new vaccine record
            $vaccine = Vaccine::create([
                'vaccine_name' => $request->vaccine_name,
                'doses_description' => $request->description ?? '',
                'stocks' => $request->stocks,
                'doses_used_from_current_bottles' => 0, // Initialize counter
            ]);

            // Clear the vaccine stocks cache
            Cache::forget('vaccine_stocks_list');

            return response()->json([
                'success' => true,
                'message' => 'Vaccine added successfully',
                'vaccine' => $vaccine
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the vaccine: ' . $e->getMessage()
            ], 500);
        }
    }
}