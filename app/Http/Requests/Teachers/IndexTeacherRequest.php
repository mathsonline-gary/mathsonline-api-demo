<?php

namespace App\Http\Requests\Teachers;

use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexTeacherRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search_key' => ['string'],
        ];
    }
}
