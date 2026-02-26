<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VendorTransaction;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VendorTransactionController extends Controller
{
    /**
     * XLSX Bulk Upload Method
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_data' => 'required|array', // Parsed data from frontend
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 1. Create a new batch
        $batch = Batch::create([
            'upload_date' => Carbon::today(),
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $rows = $request->file_data;
            $chunks = array_chunk($rows, 500); // Split into chunks of 500 records

            DB::beginTransaction();
            $totalCount = 0;

            foreach ($chunks as $chunk) {
                $batchData = [];

                foreach ($chunk as $row) {
                    $batchData[] = [
                        'batch_id'   => $batch->id,
                        'wallet_id'  => $row['wallet_id'], // Make sure wallet_id is coming as a valid ID
                        'trx_id'     => $row['trx_id'],
                        'sender_no'  => $row['sender_no'],
                        'trx_date'   => Carbon::parse($row['trx_date']),
                        'amount'     => $row['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                VendorTransaction::insert($batchData); // High-speed bulk insert
                $totalCount += count($batchData);
            }

            // 2. Update batch after successful upload
            $batch->update([
                'vendor_file_count' => $totalCount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Batch #$batch->id: $totalCount Vendor Transactions uploaded.",
                'batch_id' => $batch->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $batch->update(['status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CRUD: Index with Pagination
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => VendorTransaction::with(['wallet', 'batch'])
                ->latest()
                ->paginate(50)
        ]);
    }

    /**
     * CRUD: Show single transaction
     */
    public function show(VendorTransaction $vendorTransaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $vendorTransaction->load(['wallet', 'batch'])
        ]);
    }

    /**
     * CRUD: Update transaction
     */
    public function update(Request $request, VendorTransaction $vendorTransaction): JsonResponse
    {
        $vendorTransaction->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully'
        ]);
    }

    /**
     * CRUD: Delete transaction
     */
    public function destroy(VendorTransaction $vendorTransaction): JsonResponse
    {
        $vendorTransaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}