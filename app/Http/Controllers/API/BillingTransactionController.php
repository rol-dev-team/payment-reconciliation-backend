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
    public function bulkUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_data' => 'required|array',
            'billing_system_id' => 'required|exists:billing_systems,id',
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
                        'billing_system_id' => $request->billing_system_id,
                        'row_index' => $currentRow++,
                        'trx_id' => $row['trx_id'],
                        'customer_id' => $row['customer_id'] ?? null,
                        'sender_no' => $row['sender_no'] ?? null,
                        'amount' => $row['amount'] ?? 0,
                        'trx_date' => $trxDate,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ];
                }

                if (!empty($data)) {
                    BillingTransaction::upsert($data, ['trx_id','billing_system_id'], ['amount','trx_date','customer_id','sender_no','updated_at']);
                    $totalCount += count($data);
                }
            }

            $batch->update([
                'billing_file_count' => $totalCount,
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
        return response()->json(['success'=>true,'data'=>BillingTransaction::with(['billingSystem','batch'])->orderBy('row_index')->paginate(50)]);
    }

    public function show(BillingTransaction $billingTransaction): JsonResponse
    {
        return response()->json(['success'=>true,'data'=>$billingTransaction->load(['billingSystem','batch'])]);
    }

    public function update(Request $request, BillingTransaction $billingTransaction): JsonResponse
    {
        $billingTransaction->update($request->all());
        return response()->json(['success'=>true,'message'=>'Updated successfully']);
    }

    public function destroy(BillingTransaction $billingTransaction): JsonResponse
    {
        $billingTransaction->delete();
        return response()->json(['success'=>true,'message'=>'Deleted successfully']);
    }
}