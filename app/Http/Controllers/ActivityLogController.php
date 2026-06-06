<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(20)
            ->through(function ($log) {
                // mask numbers and descriptions so full logs never hit the frontend
                $maskedCardNumber = '****-****-****-'.substr($log->card_number, -4);

                $maskedDescription = preg_replace_callback('/\b\d{16,20}\b/', fn ($matches) => '****-****-****-'.substr($matches[0], -4), $log->description);

                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'action' => $log->action,
                    'card_number' => $maskedCardNumber,
                    'description' => $maskedDescription,
                    'created_at' => $log->created_at,
                    'user' => $log->user,
                ];
            });

        return Inertia::render('History/Index', ['logs' => $logs]);
    }

    public function exportCsv()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=activity_history_'.date('Y-m-d').'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Date', 'User ID', 'Action', 'Card Number', 'Description']);

            ActivityLog::with('user')->latest()->chunk(250, function ($logs) use ($file) {
                foreach ($logs as $log) {
                    $maskedCardNumber = '****-****-****-'.substr($log->card_number, -4);

                    $maskedDescription = preg_replace_callback('/\b\d{16,20}\b/', function ($matches) {
                        return '****-****-****-'.substr($matches[0], -4);
                    }, $log->description);

                    fputcsv($file, [
                        $log->id,
                        $log->created_at->toDateTimeString(),
                        $log->user_id,
                        $log->action,
                        $maskedCardNumber,
                        $maskedDescription,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
