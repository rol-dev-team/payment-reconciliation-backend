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
    public function bulkUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_data' => 'required|array',
            'wallet_id' => 'required|exists:wallets,id',
            'start_date'=> 'nullable|date',
            'end_date'=> 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false,'errors'=>$validator->errors()],422);
        }

        $batch = Batch::create([
            'start_date'=> $request->start_date ?? Carbon::today(),
            'end_date'=> $request->end_date ?? Carbon::today(),
            'status'=> 'processing',
            'started_at'=> now(),
        ]);

        $rows = $request->file_data;
        $chunks = array_chunk($rows, 500);
        $totalCount = 0;
        $currentRow = 1;

        DB::beginTransaction();

        try {
            foreach ($chunks as $chunk) {
                $data = [];
                foreach ($chunk as $row) {
                    try {
                        $trxDate = isset($row['trx_date']) ? Carbon::parse($row['trx_date']) : null;
                    } catch (\Exception $e) {
                        $trxDate = null;
                    }

                    $data[] = [
                        'batch_id' => $batch->id,
                        'wallet_id' => $request->wallet_id,
                        'row_index' => $currentRow++,
                        'trx_id' => $row['trx_id'],
                        'sender_no' => $row['sender_no'] ?? null,
                        'amount' => $row['amount'] ?? 0,
                        'trx_date' => $trxDate,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ];
                }

                if (!empty($data)) {
                    VendorTransaction::upsert($data, ['trx_id','wallet_id'], ['amount','trx_date','sender_no','updated_at']);
                    $totalCount += count($data);
                }
            }

            $batch->update([
                'vendor_file_count' => $totalCount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>"Successfully processed $totalCount records in Batch #{$batch->id}",
                'batch_id'=>$batch->id
            ],201);

        } catch (\Exception $e) {
            DB::rollBack();
            $batch->update(['status'=>'failed']);
            return response()->json(['success'=>false,'message'=>"Error at row $currentRow: ".$e->getMessage()],500);
        }
    }

    /** CRUD METHODS **/

    public function index(): JsonResponse
    {
        return response()->json(['success'=>true,'data'=>VendorTransaction::with(['wallet','batch'])->orderBy('row_index')->paginate(50)]);
    }

    public function show(VendorTransaction $vendorTransaction): JsonResponse
    {
        return response()->json(['success'=>true,'data'=>$vendorTransaction->load(['wallet','batch'])]);
    }

    public function update(Request $request, VendorTransaction $vendorTransaction): JsonResponse
    {
        $vendorTransaction->update($request->all());
        return response()->json(['success'=>true,'message'=>'Updated successfully']);
    }

    public function destroy(VendorTransaction $vendorTransaction): JsonResponse
    {
        $vendorTransaction->delete();
        return response()->json(['success'=>true,'message'=>'Deleted successfully']);
    }
}