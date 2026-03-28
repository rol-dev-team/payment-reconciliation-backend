<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ComparisonHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReconciliationSummaryController extends Controller
{
    // channel_id => column label
    const CHANNEL_MAP = [
        1 => 'bkash_paybill',
        2 => 'bkash_pgw',
        3 => 'nagad_paybill',
        4 => 'nagad_pgw',
        5 => 'ssl',
    ];

    public function getSummary(Request $request): JsonResponse
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date'   => 'required|date|after_or_equal:start_date',
    ]);

    // Get one row per batch — latest process_no only
    $batches = DB::table('batches')
        ->whereBetween('start_date', [
            $request->input('start_date'),
            $request->input('end_date'),
        ])
        ->orderBy('start_date', 'asc')
        ->get();

    $summary = [];

    foreach ($batches as $batch) {
        // Always read from comparisons — this is the current live state
        $rows = \App\Models\Comparison::where('batch_id', $batch->id)->get();

        if ($rows->isEmpty()) continue;

        $total   = $rows->count();
        $matched = $rows->where('status', 'matched')->count();

        $mismatch = [
            'bkash_paybill' => 0,
            'bkash_pgw'     => 0,
            'nagad_paybill' => 0,
            'nagad_pgw'     => 0,
            'ssl'     => 0,
            'own_db'        => 0,
        ];

        foreach ($rows->where('status', 'mismatch') as $row) {
            if (!$row->is_vendor) {
                $mismatch['own_db']++;
            } else {
                $label = self::CHANNEL_MAP[$row->channel_id] ?? null;
                if ($label) $mismatch[$label]++;
            }
        }

        // Get the latest process_no for this batch
        $latestProcessNo = $rows->max('process_no');

        $summary[] = [
            'batch_id'     => $batch->id,
            'process_no'   => $latestProcessNo,
            'start_date'   => $batch->start_date,
            'end_date'     => $batch->end_date,
            'transactions' => $total,
            'matched'      => $matched,
            'mismatch'     => array_merge($mismatch, [
                'total' => array_sum($mismatch),
            ]),
        ];
    }

    return response()->json([
        'success' => true,
        'data'    => $summary,
    ]);
}
}