<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use Stripe\Subscription;

class SubscriptionController extends Controller
{
    public function store(StoreSubscriptionRequest $request)
    {
        $this->authorize('create', Subscription::class);
    }
}
