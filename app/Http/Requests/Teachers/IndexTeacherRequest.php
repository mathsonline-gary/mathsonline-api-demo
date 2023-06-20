<?php

namespace App\Http\Requests\Teachers;

use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /* @var User $user */
        $user = $this->user();

        return $user->can('viewAny', Teacher::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'search_key' => ['string'],
        ];
    }
}