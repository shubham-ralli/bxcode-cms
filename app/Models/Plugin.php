<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'path',
        'is_active',
        'class',
        'description',
        'version',
        'author'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
