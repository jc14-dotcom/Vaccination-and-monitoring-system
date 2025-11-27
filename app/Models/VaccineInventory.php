<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineInventory extends Model
{
    use HasFactory;

    protected $table = 'vaccine_inventory';

    protected $fillable = [
        'vaccine_id',
        'doses_per_bottle',
        'bottles_total',
        'bottles_used',
        'doses_used',
        'received_date',
        'created_by',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'doses_per_bottle' => 'integer',
        'bottles_total' => 'integer',
        'bottles_used' => 'integer',
        'doses_used' => 'integer',
    ];

    // Relationships
    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class, 'vaccine_id');
    }

    public function creator()
    {
        return $this->belongsTo(HealthWorker::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(HealthWorker::class, 'updated_by');
    }

    public function transactions()
    {
        return $this->hasMany(VaccinationTransaction::class, 'inventory_id');
    }

    // Computed Properties (Accessors)
    
    /**
     * Get total doses in this inventory batch
     */
    public function getTotalDosesAttribute()
    {
        return $this->bottles_total * $this->doses_per_bottle;
    }

    /**
     * Get available doses remaining
     */
    public function getAvailableDosesAttribute()
    {
        return $this->total_doses - $this->doses_used;
    }

    /**
     * Get available bottles remaining (calculated)
     */
    public function getAvailableBottlesAttribute()
    {
        return $this->bottles_total - $this->bottles_used;
    }

    /**
     * Get stock status: 'out' (0 doses), 'low' (1-9 doses), 'adequate' (10+ doses)
     */
    public function getStatusAttribute()
    {
        $available = $this->available_doses;
        
        if ($available <= 0) {
            return 'out';
        } elseif ($available < 10) {
            return 'low';
        } else {
            return 'adequate';
        }
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute()
    {
        $status = $this->status;
        
        return match($status) {
            'out' => 'Out of Stock',
            'low' => 'Low Stock',
            'adequate' => 'Adequate Stock',
            default => 'Unknown',
        };
    }

    /**
     * Get status color class for UI
     */
    public function getStatusColorAttribute()
    {
        $status = $this->status;
        
        return match($status) {
            'out' => 'red',
            'low' => 'yellow',
            'adequate' => 'green',
            default => 'gray',
        };
    }

    /**
     * Scope to get only inventory with available doses
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('(bottles_total * doses_per_bottle) - doses_used > 0');
    }

    /**
     * Scope to order by FIFO (oldest first)
     */
    public function scopeFifo($query)
    {
        return $query->orderBy('received_date', 'asc');
    }
}
