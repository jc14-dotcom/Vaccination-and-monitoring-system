<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class HealthWorker extends Authenticatable
{
    use HasFactory, Notifiable, HasPushSubscriptions;

    protected $table = 'health_workers'; // Specify the table name

    protected $fillable = [
        'username',
        'password',
        'email',
        'barangay_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the barangay this health worker is assigned to.
     * NULL means RHU admin with access to all barangays.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    /**
     * Check if this health worker is an RHU admin (has access to all barangays).
     * RHU admins have barangay_id = NULL.
     */
    public function isRHU(): bool
    {
        return $this->barangay_id === null;
    }

    /**
     * Check if this health worker is a barangay worker (limited to one barangay).
     */
    public function isBarangayWorker(): bool
    {
        return $this->barangay_id !== null;
    }

    /**
     * Check if this health worker can access a specific barangay.
     * RHU admins can access all barangays.
     * Barangay workers can only access their assigned barangay.
     *
     * @param string|null $barangayName The barangay name to check
     * @return bool
     */
    public function canAccessBarangay(?string $barangayName): bool
    {
        // RHU admin can access everything
        if ($this->isRHU()) {
            return true;
        }

        // No barangay specified = allow (for general queries)
        if (empty($barangayName)) {
            return true;
        }

        // Barangay worker can only access their assigned barangay
        return $this->barangay && 
               strtolower($this->barangay->name) === strtolower($barangayName);
    }

    /**
     * Get the barangay name this worker is assigned to.
     * Returns null for RHU admins.
     */
    public function getAssignedBarangayName(): ?string
    {
        return $this->barangay?->name;
    }

    /**
     * Get list of barangay names this health worker can access.
     * RHU admins get all active barangays.
     * Barangay workers get only their assigned barangay.
     */
    public function getAccessibleBarangays(): array
    {
        if ($this->isRHU()) {
            return Barangay::getActiveNames();
        }

        return $this->barangay ? [$this->barangay->name] : [];
    }

    /**
     * Get list of barangays for vaccination schedule dropdown.
     * RHU admins get schedulable barangays + "RHU/All Barangays" option.
     * Barangay workers get only their assigned barangay (if schedulable).
     */
    public function getSchedulableBarangays(): array
    {
        if ($this->isRHU()) {
            return Barangay::getSchedulableNames();
        }

        // Barangay worker - return their barangay only if it's schedulable
        if ($this->barangay && $this->barangay->has_scheduled_vaccination) {
            return [$this->barangay->name];
        }

        return [];
    }
}
