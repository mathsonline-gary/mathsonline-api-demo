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
        Schema::create('classroom_secondary_teacher', function (Blueprint $table) {
            $table->comment('Pivot table to associate teachers with classrooms as secondary teachers.');

            $table->id();

            $table->foreignId('classroom_id')
                ->constrained('classrooms');

            $table->foreignId('teacher_id')
                ->constrained('teachers');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_secondary_teacher');
    }
};
