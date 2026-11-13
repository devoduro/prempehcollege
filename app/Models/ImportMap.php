<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportMap extends Model
{
    public $timestamps = false;

    protected $fillable = ['wp_type', 'wp_id', 'model', 'model_id', 'checksum', 'imported_at'];

    protected function casts(): array
    {
        return ['imported_at' => 'datetime'];
    }
}
