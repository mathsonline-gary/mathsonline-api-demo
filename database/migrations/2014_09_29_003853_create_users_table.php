<?php

use App\Enums\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('login')
                ->comment('The login identifier of the user. ' .
                    'For students and teachers, this is the username. ' .
                    'For members, admins and developers this is the email address.');

            $table->string('email')
                ->nullable()
                ->comment('The email address of the user. ' .
                    'For students and teachers, this is nullable. ' .
                    'For members, admins and developers, this is required.');

            $table->string('password');

            $table->enum('type', array_column(UserType::cases(), 'value'))
                ->comment('The type of the user. 1 = student, 2 = teacher, 3 = member, 4 = admin, 5 = developer.');

            $table->string('oauth_google_id')
                ->nullable()
                ->unique()
                ->comment('The Google ID of the user for OAuth.');

            $table->timestamp('email_verified_at')
                ->nullable()
                ->comment('The timestamp when the email address was verified.');

            $table->string('remember_token')
                ->nullable()
                ->comment('The token used for "remember me" functionality.');

            $table->softDeletes();
        });

        // Create indexes.
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['login', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
