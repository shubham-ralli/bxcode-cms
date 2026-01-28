<?php

namespace Plugins\ACF\src\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = 'acf_fields';
    protected $guarded = [];
    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(FieldGroup::class, 'group_id');
    }
}
