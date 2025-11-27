<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Channels\FcmChannel;

/**
 * Notification sent when a vaccination schedule is cancelled
 * 
 * DEPLOYMENT NOTE:
 * ShouldQueue is commented out for development (sync queue has serialization issues)
 * Uncomment for production when using QUEUE_CONNECTION=database
 * 
 * DEVELOPMENT: Processes immediately (no queue)
 * PRODUCTION: Uncomment 'implements ShouldQueue' and use QUEUE_CONNECTION=database
 * 
 * Notification Channels:
 * - 'database': Stores in notifications table for in-app polling
 * - WebPushChannel: Sends PWA push notifications (requires HTTPS)
 * - SMS: Available via toSms() method (disabled by default, costs ₱0.65/SMS)
 * 
 * UPDATED: Generic announcement format (no patient-specific data)
 * Each parent receives ONE notification per schedule regardless of number of children
 */
class VaccinationScheduleCancelled extends Notification // implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $vaccinationSchedule,
        public $reason = null
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
            'Ang schedule ng bakuna sa %s ay nakansela. Petsa: %s%s',
            $this->vaccinationSchedule->barangay,
            $date,
            $this->reason ? '. Dahilan: ' . $this->reason : ''
        );
        
        return [
            'type' => 'vaccination_schedule_cancelled',
            'title' => 'Nakansela ang Schedule ng Bakuna',
            'message' => $message,
            'vaccination_schedule_id' => $this->vaccinationSchedule->id,
            'vaccination_date' => $this->vaccinationSchedule->vaccination_date,
            'barangay' => $this->vaccinationSchedule->barangay,
            'vaccine_type' => $this->vaccinationSchedule->vaccine_type,
            'reason' => $this->reason,
            'icon' => 'x-circle',
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
        
        $message = sprintf(
            "NAKANSELA: Ang schedule ng bakuna sa %s ay nakansela.\n\nPetsa: %s\nOras: %s",
            $this->vaccinationSchedule->barangay,
            $date,
            $time
        );

        if ($this->reason) {
            $message .= "\n\nDahilan: " . $this->reason;
        }

        $message .= "\n\nPakitawagan ang Health Center para sa bagong schedule.";

        return $message;
    }

    /**
     * Get the WebPush message.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        $date = \Carbon\Carbon::parse($this->vaccinationSchedule->vaccination_date)->format('F d, Y');
        $time = $this->vaccinationSchedule->vaccination_time ?: '7:00 AM';
        
        $body = sprintf(
            'Ang schedule ng bakuna sa %s ay nakansela. Petsa: %s, Oras: %s',
            $this->vaccinationSchedule->barangay,
            $date,
            $time
        );

        if ($this->reason) {
            $body .= '. Dahilan: ' . $this->reason;
        }

        return (new WebPushMessage())
            ->title('Nakansela ang Schedule ng Bakuna')
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
            'Ang schedule ng bakuna sa %s ay nakansela. Petsa: %s, Oras: %s',
            $this->vaccinationSchedule->barangay,
            $date,
            $time
        );

        if ($this->reason) {
            $body .= '. Dahilan: ' . $this->reason;
        }

        return [
            'title' => 'Nakansela ang Schedule ng Bakuna',
            'body' => $body,
            'data' => [
                'type' => 'vaccination_schedule_cancelled',
                'vaccination_schedule_id' => (string) $this->vaccinationSchedule->id,
                'vaccination_date' => $this->vaccinationSchedule->vaccination_date,
                'barangay' => $this->vaccinationSchedule->barangay,
                'action_url' => route('parent.dashboard'),
            ],
        ];
    }
}
