<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('acf_fields') && !Schema::hasColumn('acf_fields', 'placeholder')) {
            Schema::table('acf_fields', function (Blueprint $table) {
                $table->string('placeholder')->nullable()->after('default_value');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('acf_fields') && Schema::hasColumn('acf_fields', 'placeholder')) {
            Schema::table('acf_fields', function (Blueprint $table) {
                $table->dropColumn('placeholder');
            });
        }
    }
};
