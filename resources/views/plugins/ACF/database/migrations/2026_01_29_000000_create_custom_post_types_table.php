<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('custom_post_types')) {
            Schema::create('custom_post_types', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();  // e.g., 'book', 'movie'
                $table->string('plural_label');   // e.g., 'Books'
                $table->string('singular_label'); // e.g., 'Book'
                $table->json('labels')->nullable();  // All custom labels
                $table->json('supports')->nullable();  // title, editor, featured_image, etc.
                $table->json('settings')->nullable();  // Advanced settings (visibility, urls, etc.)
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('custom_post_types');
    }
};
