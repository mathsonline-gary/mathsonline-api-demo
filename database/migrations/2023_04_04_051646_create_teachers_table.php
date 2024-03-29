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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users');
            $table->string('username')
                ->unique();
            $table->string('email')
                ->unique()
                ->nullable();
            $table->string('first_name')
                ->nullable();
            $table->string('last_name')
                ->nullable();
            $table->string('title', 16)
                ->nullable();
            $table->string('position')
                ->nullable();
            $table->boolean('is_admin')
                ->default(false)
                ->comment('Indicate whether this teacher has the administrator access.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
