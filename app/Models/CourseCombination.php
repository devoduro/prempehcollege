<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property list<string> $subjects */
class CourseCombination extends Model
{
    protected $fillable = ['programme_id', 'name', 'subjects', 'notes', 'sort_order'];

    protected function casts(): array
    {
        return ['subjects' => 'array'];
    }

    /** @return BelongsTo<Programme, $this> */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
}
