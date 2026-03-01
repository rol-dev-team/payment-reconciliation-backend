<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\VendorFile;
use App\Models\BillingFile;
use App\Models\VendorTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\VendorNormalizationService;

class ReconcileController extends Controller
{
    public function reconcile(Request $request): JsonResponse
    {
        $request->validate([
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
            'service_files'        => 'required|array',
            'service_files.*'      => 'required|file|mimes:xlsx,xls,csv',
            'service_channel_id'   => 'required|array',
            'service_channel_id.*' => 'required|exists:payment_channels,id',
            'service_wallet_id'    => 'required|array',
            'service_wallet_id.*'  => 'required|exists:wallets,id',
            'billing_files'        => 'required|array',
            'billing_files.*'      => 'required|file|mimes:xlsx,xls,csv',
            'billing_system_id'    => 'required|array',
            'billing_system_id.*'  => 'required|exists:billing_systems,id',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Create batch
            $batch = Batch::create([
                'start_date'         => $request->input('start_date'),
                'end_date'           => $request->input('end_date'),
                'vendor_file_count'  => count($request->file('service_files')),
                'billing_file_count' => count($request->file('billing_files')),
                'status'             => 'pending',
                'started_at'         => now(),
            ]);

            $baseFolder = "batch-{$batch->id}";
            $normalizer = new VendorNormalizationService();

            // 2️⃣ Process vendor/service files
            foreach ($request->file('service_files') as $i => $file) {

                $path = $file->store("{$baseFolder}/vendor_files", 'private');

                $vendorFile = VendorFile::create([
                    'batch_id'          => $batch->id,
                    'channel_id'        => $request->input('service_channel_id')[$i],
                    'wallet_id'         => $request->input('service_wallet_id')[$i],
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_path'       => $path,
                ]);

                // ✅ Pass wallet_id as 3rd argument
                $normalizedRows = $normalizer->normalize(
                    $path,
                    $vendorFile->channel_id,
                    $vendorFile->wallet_id
                );

                $bulkInsert = [];
                foreach ($normalizedRows as $index => $row) {
                    $bulkInsert[] = [
                        'batch_id'  => $vendorFile->batch_id,
                        'wallet_id' => $vendorFile->wallet_id,
                        'trx_id'    => $row['trx_id'],
                        'sender_no' => $row['sender_no'],
                        'trx_date'  => $row['trx_date'],
                        'amount'    => $row['amount'],
                        'row_index' => $index + 1, // ← Add this to preserve sequence
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ];
                }

                if (!empty($bulkInsert)) {
                    VendorTransaction::insert($bulkInsert);
                }

            }

            // 3️⃣ Store billing files
            foreach ($request->file('billing_files') as $i => $file) {
                $path = $file->store("{$baseFolder}/billing_files", 'private');

                BillingFile::create([
                    'batch_id'          => $batch->id,
                    'billing_system_id' => $request->input('billing_system_id')[$i],
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_path'       => $path,
                ]);
            }

            // 4️⃣ Commit
            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Batch created successfully',
                'batch_id' => $batch->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create batch',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
