<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::dropIfExists('custom_taxonomies');

        Schema::create('custom_taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'genre', 'topic'
            $table->string('plural_label');  // e.g., 'Genres'
            $table->string('singular_label'); // e.g., 'Genre'
            $table->boolean('hierarchical')->default(true); // true = Category-like, false = Tag-like
            $table->json('post_types')->nullable(); // ["post", "book"]
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_taxonomies');
    }
};
