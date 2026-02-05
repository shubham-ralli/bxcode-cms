<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('acf_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('acf_fields', 'placeholder')) {
                $table->string('placeholder')->nullable()->after('default_value');
            }
        });
    }

    public function down()
    {
        Schema::table('acf_fields', function (Blueprint $table) {
            $table->dropColumn('placeholder');
        });
    }
};
