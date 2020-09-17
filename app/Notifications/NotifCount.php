<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifCount extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $full_name;
    protected $id_reference;
    protected $id_subreference;
    protected $display;
    protected $type;
    protected $totalCount;
    public function __construct($data)
    {
        $this->id_reference = $data['id_reference'];
        $this->id_subreference = $data['id_subreference'];
        $this->display = $data['display'];
        $this->fullname = $data['full_name'];
        $this->type = $data['type'];
        $this->totalCount = $data['totalCount'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id_reference' => $this->id_reference,
            'id_subreference' => $this->id_subreference,
            'display' => $this->display,
            'full_name' => $this->full_name,
            'type' => $this->type,
            'totalCount' => $this->totalCount
        ];
    }
}
