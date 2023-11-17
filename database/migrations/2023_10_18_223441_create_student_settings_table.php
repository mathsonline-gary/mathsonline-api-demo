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
        Schema::create('student_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students');

            $table->boolean('expired_tasks_excluded')
                ->default(true)
                ->comment('Whether this student should receive expired tasks from the classroom group. The student will still receive active and future tasks.');


            $table->boolean('balloon_tips_enabled')
                ->default(true)
                ->comment('Whether this student should receive balloon tips.');

            $table->boolean('results_enabled')
                ->default(false)
                ->comment('Whether this student should see the results.');

            $table->boolean('confetti_enabled')
                ->default(true)
                ->comment('Whether celebrate achievements by displaying confetti.');

            $table->string('background_color', 6)
                ->default('F8F8F8')
                ->comment('The HEX background colour for the student.');

            $table->string('accent_color', 6)
                ->default('1E90FF')
                ->comment('The HEX accent colour for the student.');

            $table->string('closed_captions_language')
                ->default('en')
                ->comment('The language code for closed captions.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_settings');
    }
};
