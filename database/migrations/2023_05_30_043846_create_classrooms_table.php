<?php

use App\Models\Classroom;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools');

            $table->foreignId('owner_id')
                ->constrained('teachers');

            $table->enum('type', [
                Classroom::TRADITIONAL_CLASSROOM,
                Classroom::HOMESCHOOL_CLASSROOM,
            ]);

            $table->string('name');

            $table->integer('pass_grade')
                ->default(0)
                ->comment('The minimum grade that students in this class should score to pass tasks.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
