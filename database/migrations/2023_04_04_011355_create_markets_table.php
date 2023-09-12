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
        Schema::create('markets', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('country');

            $table->string('country_code');

            $table->string('timezone');

            $table->string('product');

            $table->string('website');

            $table->string('domain');

            $table->string('marketing_domain');

            $table->string('info_email');

            $table->string('feedback_email');

            $table->string('no_reply_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
