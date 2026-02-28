<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;

class BatchController extends Controller
{
    public function index(): JsonResponse
    {
        $batches = Batch::withCount(['billingTransactions', 'vendorTransactions'])
            ->with(['vendorFiles', 'billingFiles'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $batches,
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $batch = Batch::withCount(['billingTransactions', 'vendorTransactions'])
            ->with(['vendorFiles', 'billingFiles'])
            ->find($id);

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'details'      => $batch,
                'vendor_files' => $batch->vendorFiles,
                'billing_files'=> $batch->billingFiles,
                'summary'      => [
                    'vendor_file_count'  => $batch->vendor_file_count,
                    'billing_file_count' => $batch->billing_file_count,
                    'vendor_transactions'  => $batch->vendor_transactions_count,
                    'billing_transactions' => $batch->billing_transactions_count,
                ],
            ],
        ], 200);
    }

    public function destroy(Batch $batch): JsonResponse
    {
        $batch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Batch and all related transactions deleted successfully',
        ], 200);
    }
}