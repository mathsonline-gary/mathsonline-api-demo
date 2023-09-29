<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            $table->foreignId('market_id')
                ->constrained('markets');

            $table->string('name');

            $table->enum('type_id', [
                School::TYPE_TRADITIONAL_SCHOOL,
                School::TYPE_HOMESCHOOL,
            ])
                ->comment('1: Traditional School, 2: Homeschool');

            $table->string('email')
                ->nullable();

            $table->string('phone')
                ->nullable();

            $table->string('fax')
                ->nullable();

            $table->string('address_line_1')
                ->nullable();

            $table->string('address_line_2')
                ->nullable();

            $table->string('address_city')
                ->nullable();

            $table->string('address_state')
                ->nullable();

            $table->string('address_postal_code')
                ->nullable();

            $table->string('address_country')
                ->nullable();

            $table->timestamps();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('school_id')
                ->constrained('schools');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('school_id')
                ->constrained('schools');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('school_id')
                ->constrained('schools');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        Schema::dropIfExists('schools');
    }
};
