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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('role')->default('subscriber')->after('email'); // subscriber, contributor, author, editor, administrator
            $table->text('bio')->nullable()->after('password');
            $table->unsignedBigInteger('profile_image_id')->nullable()->after('bio');

            // Optional: Foreign key constraint if media table is guaranteed to exist
            // $table->foreign('profile_image_id')->references('id')->on('media')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'role', 'bio', 'profile_image_id']);
        });
    }
};
