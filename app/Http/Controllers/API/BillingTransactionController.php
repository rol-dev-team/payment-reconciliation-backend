<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BillingTransaction;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BillingTransactionController extends Controller
{
    /**
     * Professional method for uploading XLSX/Excel data in bulk with Sequence Tracking
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'billing_system_id' => 'required|exists:billing_systems,id',
            'file_data' => 'required|array', // Parsed array from frontend
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 1. Create a batch and start tracking
        $batch = Batch::create([
            'upload_date' => Carbon::today(),
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $rows = $request->file_data;
            $chunks = array_chunk($rows, 1000); // Increased chunk size for better performance

            DB::beginTransaction();
            
            $totalCount = 0;
            $currentRow = 1; // Excel row sequence tracking শুরু

            foreach ($chunks as $chunk) {
                $batchData = [];
                foreach ($chunk as $row) {
                    $batchData[] = [
                        'billing_system_id' => $request->billing_system_id,
                        'batch_id'          => $batch->id,
                        'row_index'         => $currentRow++, // Excel সিকুয়েন্স সেভ হচ্ছে
                        'trx_id'            => $row['trx_id'],
                        'entity'            => $row['entity'] ?? null,
                        'customer_id'       => $row['customer_id'],
                        'sender_no'         => $row['sender_no'],
                        'amount'            => $row['amount'],
                        'trx_date'          => Carbon::parse($row['trx_date']),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
                // High-performance bulk insert
                BillingTransaction::insert($batchData);
                $totalCount += count($batchData);
            }

            // 2. Update batch after successful processing
            $batch->update([
                'billing_file_count' => $totalCount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully processed $totalCount records in Batch #$batch->id",
                'batch_id' => $batch->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $batch->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * CRUD: List all transactions (Sorted by row_index to match Excel)
     */
    public function index(): JsonResponse
    {
        // latest() এর বদলে row_index অনুযায়ী সাজানো হয়েছে
        $transactions = BillingTransaction::with(['billingSystem', 'batch'])
            ->orderBy('row_index', 'asc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * CRUD: Show a single transaction
     */
    public function show(BillingTransaction $billingTransaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $billingTransaction->load(['billingSystem', 'batch'])
        ]);
    }

    /**
     * CRUD: Update a transaction
     */
    public function update(Request $request, BillingTransaction $billingTransaction): JsonResponse
    {
        $billingTransaction->update($request->all());
        return response()->json(['success' => true, 'message' => 'Updated successfully']);
    }

    /**
     * CRUD: Delete a transaction
     */
    public function destroy(BillingTransaction $billingTransaction): JsonResponse
    {
        $billingTransaction->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}