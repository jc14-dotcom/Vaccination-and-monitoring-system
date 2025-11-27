<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Channels\FcmChannel;
use Carbon\Carbon;

class VaccinationReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $vaccinationSchedule,
        public $patient,
        public $daysUntil
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
        $daysText = $this->daysUntil == 1 ? 'bukas' : $this->daysUntil . ' araw';
        
        return [
            'type' => 'vaccination_reminder',
            'title' => 'Paalala: Malapit na ang Bakuna',
            'message' => sprintf(
                'Paalala: Ang bakuna para kay %s ay %s na. Petsa: %s, Oras: %s, Bakuna: %s',
                $this->patient->name,
                $daysText,
                $this->vaccinationSchedule->vaccination_date,
                $this->vaccinationSchedule->vaccination_time,
                $this->vaccinationSchedule->vaccine_type
            ),
            'patient_id' => $this->patient->id,
            'patient_name' => $this->patient->name,
            'vaccination_schedule_id' => $this->vaccinationSchedule->id,
            'vaccination_date' => $this->vaccinationSchedule->vaccination_date,
            'vaccination_time' => $this->vaccinationSchedule->vaccination_time,
            'vaccine_type' => $this->vaccinationSchedule->vaccine_type,
            'days_until' => $this->daysUntil,
            'icon' => 'bell',
            'action_url' => route('parent.dashboard'),
        ];
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        $daysText = $this->daysUntil == 1 ? 'BUKAS' : $this->daysUntil . ' ARAW';
        
        return sprintf(
            "PAALALA: Ang bakuna para kay %s ay %s na!\n\nPetsa: %s\nOras: %s\nBakuna: %s\n\nHuwag kalimutang magdala ng vaccination card. Salamat!",
            $this->patient->name,
            $daysText,
            $this->vaccinationSchedule->vaccination_date,
            $this->vaccinationSchedule->vaccination_time,
            $this->vaccinationSchedule->vaccine_type
        );
    }

    /**
     * Get the WebPush message.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        $daysText = $this->daysUntil == 1 ? 'bukas' : $this->daysUntil . ' araw';
        
        return (new WebPushMessage())
            ->title('Paalala: Malapit na ang Bakuna')
            ->icon('/images/icon-192x192.png')
            ->body(sprintf(
                'Ang bakuna para kay %s ay %s na. Petsa: %s, Oras: %s',
                $this->patient->name,
                $daysText,
                $this->vaccinationSchedule->vaccination_date,
                $this->vaccinationSchedule->vaccination_time
            ))
            ->action('Tingnan', route('parent.dashboard'))
            ->data(['id' => $this->vaccinationSchedule->id])
            ->badge('/images/badge.png');
    }

    /**
     * Get the FCM message.
     */
    public function toFcm(object $notifiable): array
    {
        $daysText = $this->daysUntil == 1 ? 'bukas' : $this->daysUntil . ' araw';
        
        return [
            'title' => 'Paalala: Malapit na ang Bakuna',
            'body' => sprintf(
                'Ang bakuna para kay %s ay %s na. Petsa: %s, Oras: %s',
                $this->patient->name,
                $daysText,
                $this->vaccinationSchedule->vaccination_date,
                $this->vaccinationSchedule->vaccination_time
            ),
            'data' => [
                'type' => 'vaccination_reminder',
                'patient_id' => (string) $this->patient->id,
                'patient_name' => $this->patient->name,
                'vaccination_schedule_id' => (string) $this->vaccinationSchedule->id,
                'vaccination_date' => $this->vaccinationSchedule->vaccination_date,
                'days_until' => (string) $this->daysUntil,
                'action_url' => route('parent.dashboard'),
            ],
        ];
    }
}
