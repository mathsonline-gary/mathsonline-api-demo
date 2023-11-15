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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();

            $table->string('code')
                ->unique()
                ->comment('The campaign code.');

            $table->string('description')
                ->nullable()
                ->comment('The campaign description.');

            $table->timestamp('expires_at')
                ->nullable()
                ->comment('The campaign expiry date.');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
