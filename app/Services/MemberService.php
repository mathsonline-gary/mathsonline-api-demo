<?php

namespace App\Services;

use App\Models\Users\Member;
use App\Models\Users\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberService
{
    /**
     * Create a new member and the associated instances.
     *
     * @param array $attributes
     *
     * @return Member
     */
    public function create(array $attributes): Member
    {
        $attributes = Arr::only($attributes, [
            'school_id',
            'email',
            'first_name',
            'last_name',
            'password',
        ]);

        $attributes['password'] = Hash::make($attributes['password']);

        return DB::transaction(function () use ($attributes) {
            // Create a user.
            $user = User::create([
                'login' => $attributes['email'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'type' => User::TYPE_MEMBER,
            ]);

            // Create a member.
            $member = new Member($attributes);

            $user->member()->save($member);

            return $member;
        });
    }
}
