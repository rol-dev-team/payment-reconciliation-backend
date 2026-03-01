<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\VendorTransaction;
use App\Models\BillingTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BatchController extends Controller
{
    public function storeAndProcess(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'service_files' => 'required|array',
            'service_files.*' => 'file|mimes:csv,xlsx,xls',
            'billing_files' => 'required|array',
            'billing_files.*' => 'file|mimes:csv,xlsx,xls',
            'service_wallet_id' => 'required|array',
            'billing_system_id' => 'required|array',
        ]);

        try {
            set_time_limit(600);
            ini_set('memory_limit', '512M');

            $batch = Batch::create([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $batch->

            $vendorTotal = 0;
            $billingTotal = 0;

            // Process Vendor Files
            foreach ($request->file('service_files') as $i => $file) {
                $walletId = $request->service_wallet_id[$i];
                $vendorTotal += $this->processVendorFile($file, $batch->id, $walletId);
            }

            // Process Billing Files
            foreach ($request->file('billing_files') as $i => $file) {
                $systemId = $request->billing_system_id[$i];
                $billingTotal += $this->processBillingFile($file, $batch->id, $systemId);
            }

            $batch->update([
                'status' => 'completed',
                'completed_at' => now(),
                'vendor_file_count' => $vendorTotal,
                'billing_file_count' => $billingTotal,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Batch #{$batch->id} processed successfully",
                'batch_id' => $batch->id,
                'vendor_rows' => $vendorTotal,
                'billing_rows' => $billingTotal
            ], 201);

        } catch (\Exception $e) {
            Log::error("Batch Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function processVendorFile($file, $batchId, $walletId): int
    {
        $rows = $this->readSpreadsheet($file);
        $inserted = 0;
        $rowIndex = 1;

        $chunks = array_chunk($rows, 500);
        foreach ($chunks as $chunk) {
            $data = [];
            foreach ($chunk as $row) {
                if (empty($row[0]) || !isset($row[1], $row[2])) continue;

                try {
                    $trxDate = $row[2] ? Carbon::parse($row[2]) : null;
                } catch (\Exception $ex) {
                    $trxDate = null;
                }

                $data[] = [
                    'batch_id' => $batchId,
                    'wallet_id' => $walletId,
                    'row_index' => $rowIndex++,
                    'trx_id' => $row[0],
                    'amount' => (float)($row[1] ?? 0),
                    'trx_date' => $trxDate,
                    'sender_no' => $row[3] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($data)) {
                VendorTransaction::upsert($data, ['trx_id', 'wallet_id'], ['amount', 'trx_date', 'sender_no', 'updated_at']);
                $inserted += count($data);
            }
        }

        return $inserted;
    }

    private function processBillingFile($file, $batchId, $systemId): int
    {
        $rows = $this->readSpreadsheet($file);
        $inserted = 0;
        $rowIndex = 1;

        $chunks = array_chunk($rows, 500);
        foreach ($chunks as $chunk) {
            $data = [];
            foreach ($chunk as $row) {
                if (empty($row[0]) || !isset($row[2], $row[3])) continue;

                try {
                    $trxDate = $row[3] ? Carbon::parse($row[3]) : null;
                } catch (\Exception $ex) {
                    $trxDate = null;
                }

                $data[] = [
                    'batch_id' => $batchId,
                    'billing_system_id' => $systemId,
                    'row_index' => $rowIndex++,
                    'trx_id' => $row[0],
                    'customer_id' => $row[1] ?? null,
                    'amount' => (float)($row[2] ?? 0),
                    'trx_date' => $trxDate,
                    'sender_no' => $row[4] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($data)) {
                BillingTransaction::upsert($data, ['trx_id', 'billing_system_id'], ['amount', 'trx_date', 'customer_id', 'sender_no', 'updated_at']);
                $inserted += count($data);
            }
        }

        return $inserted;
    }

    private function readSpreadsheet($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if ($ext === 'csv') {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                fgetcsv($handle); // skip header
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            }
        } else {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true); // preserve empty cells
            array_shift($rows); // skip header row
        }

        return $rows;
    }
}