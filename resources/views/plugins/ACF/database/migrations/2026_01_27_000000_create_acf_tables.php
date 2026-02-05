<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // 1. Field Groups
        Schema::create('acf_field_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('rules')->nullable(); // e.g., {"post_type": "page"}
            $table->boolean('active')->default(true);
            $table->integer('menu_order')->default(0);
            $table->timestamps();
        });

        // 2. Fields
        Schema::create('acf_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('acf_field_groups')->onDelete('cascade');
            $table->string('label');
            $table->string('name'); // variable name
            $table->string('type'); // text, textarea, image, etc.
            $table->text('instructions')->nullable();
            $table->boolean('required')->default(false);
            $table->string('default_value')->nullable();
            $table->json('options')->nullable();
            $table->integer('menu_order')->default(0);
            $table->timestamps();
        });

        // 3. Values (Polymorphic-ish, or just Entity ID)
        Schema::create('acf_values', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->index(); // App\Models\Post
            $table->unsignedBigInteger('entity_id')->index();
            $table->string('field_name')->index();
            $table->longText('value')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate values for same field/entity
            $table->unique(['entity_type', 'entity_id', 'field_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('acf_values');
        Schema::dropIfExists('acf_fields');
        Schema::dropIfExists('acf_field_groups');
    }
};
