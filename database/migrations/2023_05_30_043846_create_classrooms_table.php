<?php

use App\Enums\ClassroomType;
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

            $table->foreignId('year_id')
                ->constrained('years');

            $table->unsignedBigInteger('owner_id')
                ->nullable()
                ->comment('The owner of the classroom. If the classroom is a traditional classroom, the key will be a teacher ID, If the classroom is a homeschool classroom, the key will be a member ID.');

            $table->enum('type', array_column(ClassroomType::cases(), 'value'))
                ->comment('1: Traditional Classroom, 2: Homeschool Classroom');

            $table->string('name');

            $table->boolean('mastery_enabled')
                ->default(false)
                ->comment('Whether to enable mastery to encourage students to master the lessons by doing more questions.');

            $table->boolean('self_rating_enabled')
                ->default(false)
                ->comment('Whether to enable self-rating emojis to let students rate their understanding for each lesson. The self-rating has no effect on student grades.');

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
