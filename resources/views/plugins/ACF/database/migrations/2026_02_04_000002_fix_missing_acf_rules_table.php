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
        if (!Schema::hasTable('acf_field_group_rules')) {
            Schema::create('acf_field_group_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('acf_field_groups')->onDelete('cascade');
                $table->integer('group_index')->default(0); // For OR groups
                $table->string('param'); // post_type, page_template, etc
                $table->string('operator'); // ==, !=
                $table->string('value'); // post, default, 12, etc
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop it automatically to be safe, or do:
        // Schema::dropIfExists('acf_field_group_rules');
    }
};
