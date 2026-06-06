<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\RegistrationToken;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $cards = Card::get(['balance', 'expiration_date']);

        $activeCount = 0;
        $redeemedCount = 0;
        $expiredCount = 0;

        foreach ($cards as $card) {
            $balance = (float) $card->balance;

            if ($balance === 0.0) {
                $redeemedCount++;

                continue;
            }

            if ($card->expiration_date->isPast()) {
                $expiredCount++;

                continue;
            }

            $activeCount++;
        }

        return Inertia::render('Dashboard', [
            'totalCards' => $cards->count(),
            'totalBalance' => $cards->sum('balance'),
            'activeCards' => $activeCount,

            'cardStatuses' => [
                'labels' => ['Active', 'Redeemed', 'Expired'],
                'data' => [$activeCount, $redeemedCount, $expiredCount],
            ],

            // hardcoded booleans for the security demo
            'securityStats' => [
                'status' => 'Secure',
                'encryption' => true,
                'databaseProtection' => true,
                'loginProtection' => true,
                'suspiciousActivity' => false,
            ],
        ]);
    }

    public function generateToken()
    {
        $hasActiveToken = RegistrationToken::where('is_used', false)
            ->where('expires_at', '>', now())
            ->exists();

        if (!$hasActiveToken) {
            RegistrationToken::create([
                'token' => Str::random(20),
                'expires_at' => \Carbon\Carbon::now()->addMinutes(15)
            ]);
        }

        return redirect()->back();
    }
}
