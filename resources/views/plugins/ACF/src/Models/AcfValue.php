<?php

namespace Plugins\ACF\src\Models;

use Illuminate\Database\Eloquent\Model;

class AcfValue extends Model
{
    protected $table = 'acf_values';
    protected $guarded = [];

    // Polymorphic? Or just manually managed for simplicity
    // 'entity_type', 'entity_id', 'field_name', 'value'
}
