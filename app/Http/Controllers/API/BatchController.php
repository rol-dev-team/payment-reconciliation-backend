<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;

class BatchController extends Controller
{
    /**
     * GET: api/batches
     * Show list of all batches with billing and vendor transaction counts.
     */
    public function index(): JsonResponse
    {
        // withCount performs counting at database level, which is much faster
        $batches = Batch::withCount(['billingTransactions', 'vendorTransactions'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $batches
        ], 200);
    }

    /**
     * GET: api/batches/{id}
     * Show details of a specific batch and the number of successfully saved records.
     */
    public function show($id): JsonResponse
    {
        // 1. Find batch with actual database counts
        $batch = Batch::withCount(['billingTransactions', 'vendorTransactions'])->find($id);

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        }

        // 2. Data integrity check (advanced logic)
        // Compare expected file count with actual saved database count
        $isBillingComplete = $batch->billing_file_count === $batch->billing_transactions_count;
        $isVendorComplete = $batch->vendor_file_count === $batch->vendor_transactions_count;

        return response()->json([
            'success' => true,
            'data' => [
                'details' => $batch,
                'integrity_check' => [
                    'billing_status' => $isBillingComplete ? 'Matched' : 'Mismatch',
                    'vendor_status'  => $isVendorComplete ? 'Matched' : 'Mismatch',
                ],
                // Show the counts directly to the user
                'summary' => [
                    'expected_billing' => $batch->billing_file_count,
                    'actual_billing'   => $batch->billing_transactions_count,
                    'expected_vendor'  => $batch->vendor_file_count,
                    'actual_vendor'    => $batch->vendor_transactions_count,
                ]
            ]
        ], 200);
    }

    /**
     * DELETE: api/batches/{id}
     * When deleting a batch, related transactions will also be deleted automatically
     * because of onDelete('cascade') in the migration.
     */
    public function destroy(Batch $batch): JsonResponse
    {
        $batch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Batch and all related transactions deleted successfully'
        ], 200);
    }
}