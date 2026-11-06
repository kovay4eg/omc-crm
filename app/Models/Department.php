<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use DeletesMediaFiles;

    public static function isAdministrationName(?string $name): bool
    {
        $normalized = mb_strtolower(trim((string) $name));
        $normalized = trim((string) preg_replace('/^відділ(?:\s+|$)/iu', '', $normalized));

        return in_array($normalized, ['адміністрація', 'адміністрація установи'], true);
    }

    public function isAdministration(): bool
    {
        return self::isAdministrationName($this->name);
    }

    protected $fillable = [
        'name',
        'description',
        'photo',
        'slider_enabled',
        'slider_photos',
    ];

    protected function casts(): array
    {
        return ['slider_enabled' => 'boolean', 'slider_photos' => 'array'];
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    protected function mediaFields(): array
    {
        return ['photo', 'slider_photos'];
    }
}
