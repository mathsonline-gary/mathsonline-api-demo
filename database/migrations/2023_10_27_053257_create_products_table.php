<?php

use App\Enums\SchoolType;
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
            $table->id();

            $table->foreignId('market_id')
                ->constrained();

            $table->string('name');

            $table->string('stripe_product_id')
                ->unique()
                ->nullable()
                ->comment('The Stripe product ID.');

            $table->enum('school_type', array_column(SchoolType::cases(), 'value'))
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
