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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')
                ->constrained('users');
            $table->unsignedInteger('type')
                ->comment('The type of activity. It should be one of the activity type constants defined in the Activity model.');
            $table->json('data')
                ->nullable()
                ->comment('JSON data associate with the activity.');
            $table->timestamp('acted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
