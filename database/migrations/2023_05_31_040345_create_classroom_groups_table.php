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
        Schema::create('classroom_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('classroom_id')
                ->constrained('classrooms');

            $table->string('name');

            $table->integer('pass_grade')
                ->default(0)
                ->comment('The minimum grade that students in this class group should score to pass tasks.');

            $table->boolean('is_default')
                ->comment('Indicates whether this classroom group is the default group for the class, including all students in the class.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_groups');
    }
};
