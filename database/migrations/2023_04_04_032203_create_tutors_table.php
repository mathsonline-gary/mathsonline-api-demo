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
        Schema::create('tutors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('market_id')
                ->constrained('markets');

            $table->foreignId('type_id')
                ->constrained('tutor_types');

            $table->string('username')
                ->nullable();

            $table->string('email')
                ->nullable();

            $table->string('first_name');

            $table->string('last_name');

            $table->string('password');

            $table->timestamp('email_verified_at')
                ->nullable();

            $table->rememberToken();

            $table->timestamps();

            $table->unique(['username', 'password']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropForeign(['type_id']);
            $table->dropUnique(['username', 'password']);
        });

        Schema::dropIfExists('tutors');
    }
};
