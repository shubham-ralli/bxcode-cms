<?php

namespace Plugins\ACF\src\Models;

use Illuminate\Database\Eloquent\Model;

class FieldGroup extends Model
{
    protected $table = 'acf_field_groups';
    protected $guarded = [];
    public function locationRules()
    {
        return $this->hasMany(FieldGroupRule::class, 'group_id');
    }

    public function fields()
    {
        return $this->hasMany(Field::class, 'group_id')->orderBy('menu_order');
    }
}
