<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedUserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $type = match (get_class($user)) {
            Tutor::class => 'tutor',
            Teacher::class => 'teacher',
            Student::class => 'student',
            Admin::class => 'admin',
            Developer::class => 'developer',
            default => 'unknown',
        };

        return [
            'type' => $type,
            'guard' => Auth::getDefaultDriver(),
            'users' => $user,
        ];
    }
}
