<?php
namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\HealthWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LoginController extends Controller 
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate the input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Generate a key for tracking login attempts
        $attemptKey = 'login_attempts_' . $request->ip() . '_' . $request->username;
        $banKey = 'login_banned_' . $request->ip() . '_' . $request->username;
        $banMultiplierKey = 'login_multiplier_' . $request->ip() . '_' . $request->username;
        
        // Check if user is currently banned
        if (Cache::has($banKey)) {
            $timeLeft = Cache::get($banKey) - time();
            if ($timeLeft > 0) {
                return redirect()->back()
                    ->withInput(['username' => $request->username])
                    ->with('error', "Too many failed attempts. Please try again after {$timeLeft} seconds.");
            }
        }

        // First, check if the username exists in either table
        $parent = Parents::where('username', $request->username)->first();
        $healthWorker = HealthWorker::where('username', $request->username)->first();
        
        // If the username doesn't exist in either table
        if (!$parent && !$healthWorker) {
            $this->incrementFailedAttempts($attemptKey, $banKey, $banMultiplierKey);
            
            return redirect()->back()
                ->withInput(['username' => $request->username])
                ->with('error', $this->getErrorMessage($attemptKey, $banKey));
        }
        
        // Check if it's a parent account
        if ($parent) {
            if (Hash::check($request->password, $parent->password)) {
                // Login successful, clear the attempt counters
                $this->clearLoginAttempts($attemptKey, $banKey, $banMultiplierKey);
                
                // Perform login with proper session handling
                return $this->performLogin($request, $parent, 'parents');
            } else {
                $this->incrementFailedAttempts($attemptKey, $banKey, $banMultiplierKey);
                
                return redirect()->back()
                    ->withInput(['username' => $request->username])
                    ->with('error', $this->getErrorMessage($attemptKey, $banKey));
            }
        }
        
        // If we're here, it's a health worker account
        if (Hash::check($request->password, $healthWorker->password)) {
            // Login successful, clear the attempt counters
            $this->clearLoginAttempts($attemptKey, $banKey, $banMultiplierKey);
            
            // Perform login with proper session handling
            return $this->performLogin($request, $healthWorker, 'health_worker');
        } else {
            $this->incrementFailedAttempts($attemptKey, $banKey, $banMultiplierKey);
            
            return redirect()->back()
                ->withInput(['username' => $request->username])
                ->with('error', $this->getErrorMessage($attemptKey, $banKey));
        }
    }
    
    /**
     * Perform the actual login with proper session management
     */
    private function performLogin(Request $request, $user, string $guard)
    {
        // Regenerate session ID to prevent session fixation
        $request->session()->regenerate();
        
        // Login the user
        Auth::guard($guard)->login($user);
        
        // For parents, check additional conditions
        if ($guard === 'parents') {
            // Check if this is first login (password not changed yet)
            if (!$user->password_changed) {
                return redirect()->route('parents.first-login-change-password')
                    ->with('info', 'Welcome! For your security, please change your default password.');
            }
            
            // Check if privacy policy has been accepted
            if (!$user->privacy_policy_accepted) {
                return redirect()->route('parent.privacy.consent')
                    ->with('info', 'Please read and accept the Privacy Policy to continue.');
            }
            
            return redirect()->route('parent.dashboard');
        }
        
        // For health workers
        return redirect()->route('health_worker.dashboard');
    }
    
    private function incrementFailedAttempts($attemptKey, $banKey, $banMultiplierKey)
    {
        // Get current attempt count or initialize to 0
        $attempts = Cache::get($attemptKey, 0);
        
        // Increment attempt count
        $attempts++;
        
        // Store updated attempt count for 1 hour
        Cache::put($attemptKey, $attempts, 3600);
        
        // If we've reached 3 failed attempts
        if ($attempts >= 3) {
            // Get or initialize the multiplier (starts at 1)
            $multiplier = Cache::get($banMultiplierKey, 1);
            
            // Calculate lockout time (30 seconds × multiplier)
            $banTime = 30 * $multiplier;
            
            // Set ban until timestamp in the cache
            Cache::put($banKey, time() + $banTime, 3600);
            
            // Increment multiplier for next time
            Cache::put($banMultiplierKey, $multiplier + 1, 3600);
            
            // Reset attempt counter for next round
            Cache::put($attemptKey, 0, 3600);
        }
    }
    
    private function getErrorMessage($attemptKey, $banKey)
    {
        if (Cache::has($banKey)) {
            $timeLeft = Cache::get($banKey) - time();
            if ($timeLeft > 0) {
                return "Too many failed attempts. Please try again after {$timeLeft} seconds.";
            }
        }
        
        $attempts = Cache::get($attemptKey, 0);
        $attemptsLeft = 3 - $attempts;
        
        if ($attemptsLeft > 0) {
            return "The password you entered is incorrect. You have {$attemptsLeft} attempts remaining.";
        } else {
            return "The password you entered is incorrect.";
        }
    }
    
    private function clearLoginAttempts($attemptKey, $banKey, $banMultiplierKey)
    {
        Cache::forget($attemptKey);
        Cache::forget($banKey);
        Cache::forget($banMultiplierKey);
    }
}