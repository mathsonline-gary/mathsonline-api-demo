<?php

use App\Enums\ActionTypes;
use App\Models\Action;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();

            // morphs column to store the polymorphic relationship
            $table->morphs('actionable');

            $table->string('type');

            $table->json('data')
                ->nullable()
                ->comment('JSON data associate with the action.');

            $table->timestamp('acted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
