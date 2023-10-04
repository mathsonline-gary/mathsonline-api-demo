<?php

use App\Models\Users\User;
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
                ->comment('The login identifier of the user.');
            $table->string('password');
            $table->enum('type_id', [
                User::TYPE_STUDENT,
                User::TYPE_TEACHER,
                User::TYPE_MEMBER,
                User::TYPE_ADMIN,
                User::TYPE_DEVELOPER,
            ])->comment('The type of the user. 1 = student, 2 = teacher, 3 = member, 4 = admin, 5 = developer.');
            $table->string('oauth_google_id')
                ->nullable()
                ->unique()
                ->comment('The Google ID of the user for OAuth.');
            $table->timestamp('deleted_at')
                ->nullable();
        });

        // Create indexes.
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['login', 'type_id']);
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
