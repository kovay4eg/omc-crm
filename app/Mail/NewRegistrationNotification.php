<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewRegistrationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Registration $registration;

    public Event $event;

    public int $count;

    public function __construct(Registration $registration, Event $event)
    {
        $this->registration = $registration;
        $this->event = $event;
        $this->count = $event->registrations()->count();
    }

    public function build()
    {
        return $this->subject(
            'Нова реєстрація на ваш захід: "'.
            $this->event->title.'" - '.
            $this->event->event_date->format('d.m.Y H:i')
        )
            ->view('emails.new-registration');
    }
}
