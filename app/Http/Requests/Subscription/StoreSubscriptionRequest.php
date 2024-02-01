<?php

namespace App\Http\Requests\Subscription;

use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'membership_id' => [
                'required',
                'integer',
                Rule::exists(Membership::class, 'id'),
            ],

            'payment_method' => [
                'required',
                'string',
                Rule::in([
                    Subscription::PAYMENT_METHOD_CARD,
                    Subscription::PAYMENT_METHOD_DIRECT_DEPOSIT,
                ]),
            ],

            'payment_token_id' => [
                Rule::requiredIf($this->input('payment_method') === Subscription::PAYMENT_METHOD_CARD),
                'string',
            ],
        ];
    }

    /**
     * Validate 'membership_id' and return the valid membership.
     * It verifies whether membership is valid for the given school.
     *
     * @param School $school
     *
     * @return Membership
     */
    public function validateMembership(School $school): Membership
    {
        $membership = Membership::find($this->integer('membership_id'));

        $this->validate([
            'membership_id' => [
                function ($attribute, $value, $fail) use ($school, $membership) {
                    if (
                        $membership->product->school_type !== $school->type
                        || $membership->product->market_id !== $school->market_id
                        || !$membership->campaign->isActive()
                    ) {
                        $fail('The selected membership is invalid. Please choose a different membership.');
                    }
                },
            ],
        ]);

        return $membership;
    }
}
