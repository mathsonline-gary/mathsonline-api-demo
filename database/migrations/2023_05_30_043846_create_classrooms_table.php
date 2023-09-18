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

            $table->unsignedBigInteger('owner_id')
                ->nullable()
                ->comment('The owner of the classroom. If the classroom is a traditional classroom, the key will be a teacher ID, If the classroom is a homeschool classroom, the key will be a member ID.');

            $table->enum('type', [
                Classroom::TYPE_TRADITIONAL_CLASSROOM,
                Classroom::TYPE_HOMESCHOOL_CLASSROOM,
            ])
                ->comment('1: Traditional Classroom, 2: Homeschool Classroom');

            $table->string('name');

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
