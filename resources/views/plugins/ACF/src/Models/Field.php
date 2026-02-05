<?php

namespace Plugins\ACF\src\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = 'acf_fields';
    protected $fillable = ['group_id', 'parent_id', 'label', 'name', 'type', 'instructions', 'required', 'default_value', 'options', 'menu_order'];
    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(FieldGroup::class, 'group_id');
    }

    public function parent()
    {
        return $this->belongsTo(Field::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Field::class, 'parent_id')->orderBy('menu_order');
    }
}
