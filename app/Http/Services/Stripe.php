<?php

namespace App\Http\Service;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function stripeIntent(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $amount = $request->amount * 100;
        $currency = 'eur';

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);

        return response()->json(['client_secret' => $paymentIntent->client_secret]);
    }
}
