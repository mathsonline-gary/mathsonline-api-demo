<?php

namespace App\Http\Requests\Auth;

use App\Models\Market;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $marketId = $this->integer('market_id');
        $firstName = $this->string('first_name')->toString();
        $lastName = $this->string('last_name')->toString();
        $email = $this->string('email')->lower()->toString();
        $phone = $this->string('phone')->replaceMatches('/\D+/', '')->toString();

        $address = [
            'line_1' => $this->string('address_line_1')->toString(),
            'line_2' => $this->string('address_line_2')->isEmpty() ? null : $this->string('address_line_2')->toString(),
            'city' => $this->string('address_city')->toString(),
            'state' => $this->string('address_state')->toString(),
            'postal_code' => $this->string('address_postal_code')->toString(),
            'country' => $this->string('address_country')->toString(),
        ];

        $this->merge([
            'market_id' => $marketId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'address_line_1' => $address['line_1'],
            'address_line_2' => $address['line_2'],
            'address_city' => $address['city'],
            'address_state' => $address['state'],
            'address_postal_code' => $address['postal_code'],
            'address_country' => $address['country'],
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'market_id' => ['required', 'integer', 'between:1,' . Market::count()],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('members', 'email'),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['required', 'string', 'digits_between:8,30'],
            'address_line_1' => ['required', 'string'],
            'address_line_2' => ['string', 'nullable'],
            'address_city' => ['required', 'string', 'max:255'],
            'address_state' => ['required', 'string', 'max:255'],
            'address_postal_code' => ['required', 'string', 'max:255'],
            'address_country' => ['required', 'string', 'max:255'],
        ];
    }
}
