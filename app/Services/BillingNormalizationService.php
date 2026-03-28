<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

// class BillingNormalizationService
// {
//     // BillingNormalizationService.php

// public function normalize(string $storedPath, ?int $billingSystemId = null): array
// {
//     // $storedPath is already relative to the 'private' disk root
//     // storage_path('app/private/') + storedPath = full path
//     $fullPath = storage_path('app/private/' . $storedPath);

//     if (!file_exists($fullPath)) {
//         throw new \Exception("File not found at path: {$fullPath}");
//     }

//     $rows = Excel::toArray([], $fullPath)[0];

//     if (empty($rows) || count($rows) < 2) {
//         return []; // No header or no data rows
//     }

//     $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
//     unset($rows[0]);

//     $normalized = [];

//     foreach ($rows as $row) {
//         $row = array_map(fn($v) => trim((string)$v), $row);

//         if (count($row) !== count($header)) continue;

//         $rowData = array_combine($header, $row);

//         // Skip entirely empty rows
//         if (empty(array_filter($rowData))) continue;

//         $entry = [
//             'trx_id'      => $rowData['trx_id'] ?? null,
//             'entity'      => $rowData['entity'] ?? null,
//             'customer_id' => $rowData['customer_id'] ?? null,
//             'amount'      => isset($rowData['amount']) && $rowData['amount'] !== ''
//                                 ? floatval(str_replace(',', '', $rowData['amount']))
//                                 : null,
//             'trx_date'    => isset($rowData['trx_date']) && $rowData['trx_date'] !== ''
//                                 ? Carbon::parse($rowData['trx_date'])->format('Y-m-d H:i:s')
//                                 : null,
//         ];

//         if ($entry['trx_id'] && $entry['amount'] !== null) {
//             $normalized[] = $entry;
//         }
//     }

//     return $normalized;
// }
// }

class BillingNormalizationService
{
    public function normalize(string $storedPath, ?int $billingSystemId = null): array
    {
        $fullPath = storage_path('app/private/' . $storedPath);

        if (!file_exists($fullPath)) {
            throw new \Exception("File not found at path: {$fullPath}");
        }

        $rows = Excel::toArray([], $fullPath)[0];

        if (empty($rows) || count($rows) < 2) {
            return [];
        }

        // Header becomes lowercase: "Transaction ID" -> "transaction id"
        $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        unset($rows[0]);

        $normalized = [];

        foreach ($rows as $row) {
            $row = array_map(fn($v) => trim((string)$v), $row);
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);
            if (empty(array_filter($rowData))) continue;

            // --- START FIX FOR SSL (ID 5) ---
            if ($billingSystemId === 5) {
                // SSL specific column names and cleanup
                $trxId = ltrim($rowData['transaction id'] ?? '', "'"); 
                $amount = $rowData['amount (bdt)'] ?? null;
                $trxDate = $rowData['date time'] ?? null;
                $entity = $rowData['name'] ?? null;
                $customerId = $rowData['phone'] ?? null;
            } else {
                // Default mapping for other systems
                $trxId = $rowData['trx_id'] ?? null;
                $amount = $rowData['amount'] ?? null;
                $trxDate = $rowData['trx_date'] ?? null;
                $entity = $rowData['entity'] ?? null;
                $customerId = $rowData['customer_id'] ?? null;
            }
            // --- END FIX ---

            $entry = [
                'trx_id'      => $trxId,
                'entity'      => $entity,
                'customer_id' => $customerId,
                'amount'      => ($amount !== null && $amount !== '')
                                    ? floatval(str_replace(',', '', (string)$amount))
                                    : null,
                'trx_date'    => ($trxDate !== null && $trxDate !== '')
                                    ? Carbon::parse($trxDate)->format('Y-m-d H:i:s')
                                    : null,
            ];

            // Only push if we have a valid ID and amount
            if ($entry['trx_id'] && $entry['amount'] !== null) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }
}