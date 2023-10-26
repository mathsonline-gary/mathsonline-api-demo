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
        Schema::create('classroom_group_student', function (Blueprint $table) {
            $table->comment('Pivot table to associate students with classroom groups.');

            $table->id();

            $table->foreignId('classroom_group_id')
                ->constrained('classroom_groups');

            $table->foreignId('student_id')
                ->constrained('students');

            $table->boolean('expired_tasks_excluded')
                ->default(true)
                ->comment('Whether this student should receive expired tasks from the classroom group. The student will still receive active and future tasks.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_group_student');
    }
};
