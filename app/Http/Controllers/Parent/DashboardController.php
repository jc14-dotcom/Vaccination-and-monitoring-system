<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Dashboard Controller (Parent)
 * 
 * Handles the parent dashboard display and related functionality.
 */
class DashboardController extends Controller
{
    /**
     * Display the parent dashboard
     * 
     * Shows the authenticated parent's information and their children's data.
     * Middleware ensures user is authenticated before reaching this method.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get authenticated parent (middleware ensures this exists)
        $user = Auth::guard('parents')->user();
        
        // Fetch all patients (children) for this parent
        $patients = Patient::where('parent_id', $user->id)->get();
        
        return view('parents.parentdashboard', compact('user', 'patients'));
    }
}
