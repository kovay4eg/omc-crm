<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Rescheduled = 'rescheduled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Чернетка',
            self::Published => 'Опубліковано',
            self::Cancelled => 'Скасовано',
            self::Rescheduled => 'Перенесено',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Cancelled => 'danger',
            self::Rescheduled => 'warning',
        };
    }
}