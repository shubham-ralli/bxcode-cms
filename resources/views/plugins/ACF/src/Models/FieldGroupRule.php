<?php

namespace Plugins\ACF\src\Models;

use Illuminate\Database\Eloquent\Model;

class FieldGroupRule extends Model
{
    protected $table = 'acf_field_group_rules';
    protected $guarded = [];

    public function fieldGroup()
    {
        return $this->belongsTo(FieldGroup::class, 'group_id');
    }
}
