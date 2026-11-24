<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsaAttendance extends Model
{
    protected $fillable = ['osa_event_id', 'name', 'year_completed', 'house_id', 'email', 'phone', 'sms_status', 'sms_note', 'email_status', 'email_note', 'source', 'checked_in_at'];

    protected function casts(): array
    {
        return ['checked_in_at' => 'datetime', 'year_completed' => 'integer'];
    }

    /** @return BelongsTo<OsaEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(OsaEvent::class, 'osa_event_id');
    }

    /** @return BelongsTo<House, $this> */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /**
     * Shared by the admin table and the print/CSV views so both show the same people.
     *
     * @param  array{house?: mixed, year_from?: mixed, year_to?: mixed, sms?: mixed, email?: mixed, search?: mixed}  $f
     */
    public function scopeFiltered(Builder $q, array $f): Builder
    {
        return $q
            ->when(filled($f['house'] ?? null), fn ($q) => $q->where('house_id', (int) $f['house']))
            ->when(filled($f['year_from'] ?? null), fn ($q) => $q->where('year_completed', '>=', (int) $f['year_from']))
            ->when(filled($f['year_to'] ?? null), fn ($q) => $q->where('year_completed', '<=', (int) $f['year_to']))
            ->when(in_array($f['sms'] ?? null, ['sent', 'failed', 'pending'], true), fn ($q) => $q->where('sms_status', $f['sms']))
            ->when(in_array($f['email'] ?? null, ['sent', 'failed', 'pending'], true), fn ($q) => $q->where('email_status', $f['email']))
            ->when(filled($f['search'] ?? null), function ($q) use ($f) {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim((string) $f['search'])).'%';
                $q->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
            });
    }
}
