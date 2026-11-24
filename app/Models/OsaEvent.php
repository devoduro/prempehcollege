<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OsaEvent extends Model
{
    protected $fillable = ['title', 'slug', 'venue', 'description', 'starts_at', 'ends_at', 'registration_open'];

    protected $attributes = ['registration_open' => true];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'registration_open' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Unguessable token that goes into the QR code, so only people holding the code can register.
        static::creating(function (self $e) {
            $e->token = Str::random(32);
            $e->slug = static::uniqueSlug($e->slug ?: $e->title);
        });
    }

    /** Pretty address part, e.g. "AGM 2026" becomes agm-2026; a number is added when it is already taken. */
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text) ?: 'meeting';
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    /** @return HasMany<OsaAttendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(OsaAttendance::class);
    }

    public function registerUrl(): string
    {
        return route('osa.register', $this->slug);
    }

    /** Registration is open while the toggle is on and the meeting has not ended (one day grace). */
    public function isOpen(): bool
    {
        $end = $this->ends_at ?? $this->starts_at->copy()->endOfDay();

        return $this->registration_open && now()->lessThanOrEqualTo($end->copy()->addDay());
    }
}
