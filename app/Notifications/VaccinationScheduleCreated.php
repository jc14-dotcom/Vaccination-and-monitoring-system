<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Channels\FcmChannel;

/**
 * Notification sent when a new vaccination schedule is created
 * 
 * DEPLOYMENT NOTE:
 * ShouldQueue is commented out for development (sync queue has serialization issues)
 * Uncomment for production when using QUEUE_CONNECTION=database
 * 
 * DEVELOPMENT: Processes immediately (no queue)
 * PRODUCTION: Uncomment 'implements ShouldQueue' and use QUEUE_CONNECTION=database
 *   - Run: php artisan queue:work database --sleep=3 --tries=3
 *   - See DEPLOYMENT_QUEUE_SETUP.md for supervisor/systemd setup
 * 
 * Notification Channels:
 * - 'database': Stores in notifications table for in-app polling
 * - WebPushChannel: Sends PWA push notifications (requires HTTPS)
 * - SMS: Available via toSms() method (disabled by default, costs ₱0.65/SMS)
 * 
 * UPDATED: Generic announcement format (no patient-specific data)
 * Each parent receives ONE notification per schedule regardless of number of children
 */
class VaccinationScheduleCreated extends Notification // implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $vaccinationSchedule
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $date = \Carbon\Carbon::parse($this->vaccinationSchedule->vaccination_date)->format('F d, Y');
        $time = $this->vaccinationSchedule->vaccination_time ?: '7:00 AM';
        
        // Generic announcement format - no patient-specific data
        $message = sprintf(
            'May bagong schedule ng bakuna sa %s. Petsa: %s, Oras: %s',
            $this->vaccinationSchedule->barangay,
            $date,
            $time
        );
        
        return [
            'type' => 'vaccination_schedule_created',
            'title' => 'Bagong Schedule ng Bakuna',
            'message' => $message,
            'vaccination_schedule_id' => $this->vaccinationSchedule->id,
            'vaccination_date' => $this->vaccinationSchedule->vaccination_date,
            'vaccination_time' => $time,
            'barangay' => $this->vaccinationSchedule->barangay,
            'vaccine_type' => $this->vaccinationSchedule->vaccine_type,
            'icon' => 'calendar',
            'action_url' => route('parent.dashboard'),
        ];
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        $date = \Carbon\Carbon::parse($this->vaccinationSchedule->vaccination_date)->format('F d, Y');
        $time = $this->vaccinationSchedule->vaccination_time ?: '7:00 AM';
        
        return sprintf(
            "Maligayang araw! May bagong schedule ng bakuna sa %s.\n\nPetsa: %s\nOras: %s\n\nPakidalaw sa Health Center sa nakatakdang petsa. Salamat!",
            $this->vaccinationSchedule->barangay,
            $date,
            $time
        );
    }

    /**
     * Get the WebPush message.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        $date = \Carbon\Carbon::parse($this->vaccinationSchedule->vaccination_date)->format('F d, Y');
        $time = $this->vaccinationSchedule->vaccination_time ?: '7:00 AM';
        
        $body = sprintf(
            'May bagong schedule ng bakuna sa %s. Petsa: %s, Oras: %s',
            $this->vaccinationSchedule->barangay,
            $date,
            $time
        );
        
        return (new WebPushMessage())
            ->title('Bagong Schedule ng Bakuna')
            ->icon('/images/icon-192x192.png')
            ->body($body)
            ->action('Tingnan', route('parent.dashboard'))
            ->data(['id' => $this->vaccinationSchedule->id]);
    }

    /**
     * Get the FCM message.
     */
    public function toFcm(object $notifiable): array
    {
        $date = \Carbon\Carbon::parse($this->vaccinationSchedule->vaccination_date)->format('F d, Y');
        $time = $this->vaccinationSchedule->vaccination_time ?: '7:00 AM';
        
        $body = sprintf(
            'May bagong schedule ng bakuna sa %s. Petsa: %s, Oras: %s',
            $this->vaccinationSchedule->barangay,
            $date,
            $time
        );
        
        return [
            'title' => 'Bagong Schedule ng Bakuna',
            'body' => $body,
            'data' => [
                'type' => 'vaccination_schedule_created',
                'schedule_id' => (string) $this->vaccinationSchedule->id,
                'url' => route('parent.dashboard'),
            ]
        ];
    }
}
