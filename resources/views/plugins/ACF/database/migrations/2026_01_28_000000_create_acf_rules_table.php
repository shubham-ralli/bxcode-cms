<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('acf_field_group_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('acf_field_groups')->onDelete('cascade');
            $table->integer('group_index')->default(0); // For OR groups
            $table->string('param');
            $table->string('operator');
            $table->string('value');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acf_field_group_rules');
    }
};
