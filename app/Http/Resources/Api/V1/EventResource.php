<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $registrationsCount = (int) ($this->registrations_count ?? 0);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'event_date' => $this->event_date?->toIso8601String(),
            'status' => $this->status->value,
            'image_url' => $this->image ? route('events.image', $this->resource) : null,
            'notify_email' => $this->notify_email,
            'has_registration_button' => (bool) $this->has_registration_button,
            'registration_type' => $this->registration_type,
            'google_form_url' => $this->google_form_url,
            'max_participants' => $this->max_participants,
            'show_available_slots' => (bool) $this->show_available_slots,
            'registrations_count' => $registrationsCount,
            'available_slots' => $this->max_participants === null
                ? null
                : max(0, $this->max_participants - $registrationsCount),
            'author' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'cancellation' => $this->cancelled_at ? [
                'reason' => $this->cancel_reason,
                'is_public' => (bool) $this->cancel_public,
                'cancelled_at' => $this->cancelled_at->toIso8601String(),
            ] : null,
            'reschedule' => $this->rescheduled_at ? [
                'old_event_date' => $this->old_event_date?->toIso8601String(),
                'reason' => $this->reschedule_reason,
                'is_public' => (bool) $this->reschedule_public,
                'rescheduled_at' => $this->rescheduled_at->toIso8601String(),
            ] : null,
            'smm' => [
                'title' => $this->smm_title,
                'description' => $this->smm_description,
                'image_url' => $this->smm_image ? route('events.smm-image', $this->resource) : null,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
