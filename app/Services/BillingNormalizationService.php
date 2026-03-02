<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BillingNormalizationService
{
    // BillingNormalizationService.php

public function normalize(string $storedPath, ?int $billingSystemId = null): array
{
    // $storedPath is already relative to the 'private' disk root
    // storage_path('app/private/') + storedPath = full path
    $fullPath = storage_path('app/private/' . $storedPath);

    if (!file_exists($fullPath)) {
        throw new \Exception("File not found at path: {$fullPath}");
    }

    $rows = Excel::toArray([], $fullPath)[0];

    if (empty($rows) || count($rows) < 2) {
        return []; // No header or no data rows
    }

    $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
    unset($rows[0]);

    $normalized = [];

    foreach ($rows as $row) {
        $row = array_map(fn($v) => trim((string)$v), $row);

        if (count($row) !== count($header)) continue;

        $rowData = array_combine($header, $row);

        // Skip entirely empty rows
        if (empty(array_filter($rowData))) continue;

        $entry = [
            'trx_id'      => $rowData['trx_id'] ?? null,
            'entity'      => $rowData['entity'] ?? null,
            'customer_id' => $rowData['customer_id'] ?? null,
            'sender_no'   => $rowData['payment_number'] ?? null,
            'amount'      => isset($rowData['amount']) && $rowData['amount'] !== ''
                                ? floatval(str_replace(',', '', $rowData['amount']))
                                : null,
            'trx_date'    => isset($rowData['trx_date']) && $rowData['trx_date'] !== ''
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
