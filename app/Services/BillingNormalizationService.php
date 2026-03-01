<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BillingNormalizationService
{
    public function normalize($storedPath)
    {
        $rows = Excel::toArray([], storage_path('app/private/' . $storedPath))[0];

        $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);
        unset($rows[0]);

        $normalized = [];

        foreach ($rows as $index => $row) {

            $row = array_map(fn($v) => trim((string)$v), $row);
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);
            $rowData = array_change_key_case($rowData, CASE_LOWER);

            $entry = [
                'trx_id'      => $rowData['trx_id'] ?? null,
                'entity'      => $rowData['entity'] ?? null,
                'customer_id' => $rowData['customer_id'] ?? null,
                'sender_no'   => $rowData['payment_number'] ?? null,
                'amount'      => isset($rowData['amount'])
                                    ? floatval(str_replace(',', '', $rowData['amount']))
                                    : null,
                'trx_date'    => isset($rowData['trx_date'])
                                    ? Carbon::parse($rowData['trx_date'])->format('Y-m-d H:i:s')
                                    : null,
            ];

            if ($entry['trx_id'] && $entry['amount'] !== null) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }
}
