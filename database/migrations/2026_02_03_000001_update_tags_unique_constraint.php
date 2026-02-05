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
        Schema::table('tags', function (Blueprint $table) {
            // Drop the global unique constraint on slug
            $table->dropUnique('tags_slug_unique');
            // Add composite unique constraint
            $table->unique(['slug', 'taxonomy'], 'tags_slug_taxonomy_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_slug_taxonomy_unique');
            $table->unique('slug', 'tags_slug_unique');
        });
    }
};
