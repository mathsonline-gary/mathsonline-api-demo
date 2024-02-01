<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $schoolTypes = [
                School::TYPE_TRADITIONAL_SCHOOL,
                School::TYPE_HOMESCHOOL,
            ];

            $table->id();

            $table->foreignId('market_id')
                ->constrained('markets');

            $table->string('name');

            $table->string('stripe_id')
                ->unique()
                ->comment('The Stripe product ID.');

            $table->enum('school_type', $schoolTypes)
                ->comment('Indicates the type of school that this product is for. 1: Traditional School, 2: Homeschool');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
