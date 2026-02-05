<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('custom_taxonomies')) {
            Schema::table('custom_taxonomies', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_taxonomies', 'publicly_queryable')) {
                    $table->boolean('publicly_queryable')->default(true)->after('active');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('custom_taxonomies')) {
            Schema::table('custom_taxonomies', function (Blueprint $table) {
                if (Schema::hasColumn('custom_taxonomies', 'publicly_queryable')) {
                    $table->dropColumn('publicly_queryable');
                }
            });
        }
    }
};
