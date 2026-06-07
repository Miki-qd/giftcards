<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\ActivityLog;
use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    public function index(): Response
    {
        $search = request('search');
        $filter = request('filter');
        $sortBy = request('sort_by', 'created_at');
        $sortOrder = request('sort_order', 'desc');

        $allowedSortFields = ['expiration_date', 'balance', 'created_at', 'status'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $cards = Card::when($search, fn ($q) => $q->where('card_number', 'like', "%{$search}%"))
            ->when($filter === 'expiring_soon', fn ($q) => $q->whereBetween('expiration_date', [now(), now()->addDays(30)]))
            ->when($filter === 'expired', fn ($q) => $q->where('expiration_date', '<', now()))
            ->when($sortBy === 'status', function ($q) use ($sortOrder) {
                // custom sort for derived status fields
                $q->orderByRaw("CASE WHEN expiration_date < ? THEN 'Expired' WHEN balance > 0 AND balance < 5 THEN 'Low Balance' ELSE 'Active' END {$sortOrder}", [now()]);
            }, fn ($q) => $q->orderBy($sortBy, $sortOrder))
            ->paginate(10)
            ->withQueryString()
            ->through(function ($card) {
                $status = 'Active';

                if ($card->expiration_date->isPast()) {
                    $status = 'Expired';
                } elseif ($card->balance > 0 && $card->balance < 5) {
                    $status = 'Low Balance';
                }

                return [
                    'id' => $card->id,
                    'card_number' => '****-****-****-'.substr($card->card_number, -4),
                    'activation_date' => $card->activation_date,
                    'expiration_date' => $card->expiration_date,
                    'balance' => $card->balance,
                    'status' => $status,
                    'created_at' => $card->created_at,
                    'updated_at' => $card->updated_at,
                ];
            });

        return Inertia::render('Cards/Index', [
            'cards' => $cards,
            'filters' => [
                'search' => $search,
                'filter' => $filter,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Cards/Create');
    }

    public function store(StoreCardRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        do {
            $cardNumber = '';
            for ($i = 0; $i < 16; $i++) {
                $cardNumber .= random_int(0, 9);
            }
        } while (Card::where('card_number', $cardNumber)->exists());

        $pin = '';
        for ($i = 0; $i < 4; $i++) {
            $pin .= random_int(0, 9);
        }

        $validated['card_number'] = $cardNumber;
        $validated['pin'] = $pin;

        $card = Card::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'card_number' => $card->card_number,
            'description' => "Created gift card {$card->card_number} with balance \${$card->balance}",
            'properties' => ['new' => $card->toArray()],
        ]);

        return redirect()->route('cards.index')->with('toast', [
            'type' => 'success',
            'message' => 'Card created successfully.',
        ]);
    }

    public function edit(Card $card): Response
    {
        return Inertia::render('Cards/Edit', [
            'card' => $card,
        ]);
    }

    public function update(UpdateCardRequest $request, Card $card): RedirectResponse
    {
        $oldValues = $card->toArray();
        $card->update($request->validated());
        $newValues = $card->fresh()->toArray();

        $changes = [];
        if ($oldValues['balance'] != $newValues['balance']) {
            $diff = floatval($newValues['balance']) - floatval($oldValues['balance']);
            if ($diff > 0) {
                $changes[] = 'added $'.number_format($diff, 2).' balance';
            } else {
                $changes[] = 'changed balance to $'.number_format($newValues['balance'], 2);
            }
        }
        if ($oldValues['expiration_date'] != $newValues['expiration_date']) {
            $oldExp = substr($oldValues['expiration_date'], 0, 10);
            $newExp = substr($newValues['expiration_date'], 0, 10);
            $changes[] = "extended expiration date from {$oldExp} to {$newExp}";
        }

        $desc = "Updated gift card {$card->card_number}";
        if (! empty($changes)) {
            $desc .= ' ('.implode(', ', $changes).')';
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'card_number' => $card->card_number,
            'description' => $desc,
            'properties' => ['old' => $oldValues, 'new' => $newValues],
        ]);

        return redirect()->route('cards.index')->with('toast', [
            'type' => 'success',
            'message' => 'Card updated successfully.',
        ]);
    }

    public function destroy(Card $card): RedirectResponse
    {
        $cardData = $card->toArray();
        $cardNumber = $card->card_number;
        $card->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'card_number' => $cardNumber,
            'description' => "Deleted gift card {$cardNumber}",
            'properties' => ['old' => $cardData],
        ]);

        return redirect()->route('cards.index')->with('toast', [
            'type' => 'success',
            'message' => 'Card deleted successfully.',
        ]);
    }

    public function exportCsv()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=gift_cards_'.date('Y-m-d').'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Card Number', 'Activation Date', 'Expiration Date', 'Balance', 'Created At']);

            Card::latest()->chunk(250, function ($cards) use ($file) {
                foreach ($cards as $card) {
                    $maskedCardNumber = '****-****-****-'.substr($card->card_number, -4);
                    fputcsv($file, [
                        $card->id,
                        $maskedCardNumber,
                        $card->activation_date->toDateTimeString(),
                        $card->expiration_date->toDateString(),
                        $card->balance,
                        $card->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
