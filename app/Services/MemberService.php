<?php

namespace App\Services;

use App\Models\Users\Member;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MemberService
{
    /**
     * Create a new member.
     *
     * @param array $attributes
     * @return Member
     */
    public function create(array $attributes): Member
    {
        $attributes = Arr::only($attributes, [
            'market_id',
            'school_id',
            'type_id',
            'username',
            'email',
            'first_name',
            'last_name',
            'password',
        ]);

        $attributes['password'] = Hash::make($attributes['password']);

        $member = Member::create($attributes);

        Log::info('Member created: ', $member->only([
            'id',
            'username',
            'email',
            'first_name',
            'last_name',
        ]));

        return $member;
    }
}
