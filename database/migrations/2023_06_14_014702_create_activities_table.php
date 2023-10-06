<?php

use App\Enums\ActivityType;
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
            $table->enum('type', array_column(ActivityType::cases(), 'value'));
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
