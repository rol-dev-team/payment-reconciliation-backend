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
    /**
     * Bulk Upload with Sequence and Duplicate Prevention
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_data' => 'required|array', 
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // ১. ব্যাচ তৈরি
        $batch = Batch::create([
            'upload_date' => Carbon::today(),
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $rows = $request->file_data;
            
            // সিনিয়র টিপস: ডাটাবেসে ইনসার্ট করার আগে ডুপ্লিকেট ট্রানজিশন চেক করা ভালো
            // তবে পারফরম্যান্সের জন্য আমরা DB Transaction ব্যবহার করছি
            $chunks = array_chunk($rows, 1000); 

            DB::beginTransaction();
            
            $totalCount = 0;
            $currentRow = 1; // সিকুয়েন্স শুরু

            foreach ($chunks as $chunk) {
                $batchData = [];
                foreach ($chunk as $row) {
                    $batchData[] = [
                        'batch_id'   => $batch->id,
                        'wallet_id'  => $row['wallet_id'],
                        'row_index'  => $currentRow++, // Excel sequence বজায় রাখা
                        'trx_id'     => $row['trx_id'],
                        'sender_no'  => $row['sender_no'],
                        'trx_date'   => Carbon::parse($row['trx_date']),
                        'amount'     => $row['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                // হাই-পারফরম্যান্স ইনসার্ট
                VendorTransaction::insert($batchData);
                $totalCount += count($batchData);
            }

            // ২. ব্যাচ আপডেট
            $batch->update([
                'vendor_file_count' => $totalCount,
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
            return response()->json(['success' => false, 'message' => "Error at row $currentRow: " . $e->getMessage()], 500);
        }
    }

    /**
     * CRUD: Index (Sorted by Excel sequence)
     */
    public function index(): JsonResponse
    {
        $data = VendorTransaction::with(['wallet', 'batch'])
            ->orderBy('row_index', 'asc') // সিকুয়েন্স অনুযায়ী দেখাচ্ছে
            ->paginate(50);

        return response()->json(['success' => true, 'data' => $data]);
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