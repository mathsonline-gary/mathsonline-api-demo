<?php

namespace App\Http\Requests\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'market_id' => [
                'required',
                'integer',
                Rule::exists('markets', 'id'),
            ],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'login')
                    ->where('type', User::TYPE_MEMBER),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['required', 'string'],
            'address_line_1' => ['required', 'string'],
            'address_line_2' => ['string', 'nullable'],
            'address_city' => ['required', 'string', 'max:255'],
            'address_state' => ['required', 'string', 'max:255'],
            'address_postal_code' => ['required', 'string', 'max:255'],
            'address_country' => ['required', 'string', 'max:255'],
        ];
    }
}
