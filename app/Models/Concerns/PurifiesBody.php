<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Mews\Purifier\Facades\Purifier;

/** Rich-text bodies are purified on write so stored HTML is always safe to render. */
trait PurifiesBody
{
    protected function body(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : Purifier::clean($value, 'default'),
        );
    }
}
