<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function upcoming(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 30);

        $subscriptions = Subscription::with('site.client')
            ->whereNotNull('renewal_date')
            ->where('renewal_date', '<=', now()->addDays($days))
            ->where('renewal_date', '>=', now())
            ->whereIn('status', ['pagado', 'por_vencer'])
            ->orderBy('renewal_date')
            ->get()
            ->map(fn ($sub) => [
                'id' => $sub->id,
                'client_name' => $sub->site?->client?->name,
                'client_email' => $sub->site?->client?->email,
                'client_phone' => $sub->site?->client?->phone,
                'site_name' => $sub->site?->name,
                'service' => $sub->service_type,
                'price' => $sub->price,
                'currency' => $sub->currency,
                'billing_cycle' => $sub->billing_cycle,
                'payment_method' => $sub->payment_method,
                'payment_link' => $sub->payment_link,
                'renewal_date' => $sub->renewal_date->toDateString(),
                'days_until_renewal' => now()->diffInDays($sub->renewal_date, false),
                'status' => $sub->status,
            ]);

        return response()->json(['data' => $subscriptions]);
    }
}
