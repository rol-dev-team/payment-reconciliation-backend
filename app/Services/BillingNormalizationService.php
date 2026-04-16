<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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

        $header = array_map(fn($h) => $this->normalizeHeader($h), $rows[0]);
        unset($rows[0]);

        $normalized = [];

        foreach ($rows as $row) {
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);
            if (empty(array_filter($rowData))) continue;

            $trxId = $this->cleanString(
                $rowData['trx_id']
                ?? $rowData['transaction_id']
                ?? null
            );

            $entityId = $this->parseInteger(
                $rowData['entity_id']
                ?? null
            );

            $entity = $this->cleanString(
                $rowData['entity']
                ?? $rowData['name']
                ?? null
            );

            $customerId = $this->cleanString(
                $rowData['customer_id']
                ?? $rowData['phone']
                ?? null
            );

            $amount = $this->parseAmount(
                $rowData['amount']
                ?? $rowData['amount_bdt']
                ?? null
            );

            $trxDate = $this->parseDate(
                $rowData['trx_date']
                ?? $rowData['date_time']
                ?? null
            );

            $entry = [
                'trx_id'      => $trxId,
                'entity_id'   => $entityId,
                'entity'      => $entity,
                'customer_id' => $customerId,
                'amount'      => $amount,
                'trx_date'    => $trxDate,
            ];

            if ($entry['trx_id'] && $entry['amount'] !== null) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }

    private function normalizeHeader(mixed $header): string
    {
        $normalized = strtolower(trim((string) $header));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);

        return trim((string) $normalized, '_');
    }

    private function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $cleaned = trim((string) $value);
        $cleaned = ltrim($cleaned, "'");

        return $cleaned === '' ? null : $cleaned;
    }

    private function parseInteger(mixed $value): ?int
    {
        $cleaned = $this->cleanString($value);

        if ($cleaned === null || !is_numeric($cleaned)) {
            return null;
        }

        return (int) $cleaned;
    }

    private function parseAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $cleaned = preg_replace('/[^\d.\-]/', '', str_replace(',', '', (string) $value));

        return $cleaned === '' ? null : (float) $cleaned;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->format('Y-m-d H:i:s');
            }

            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))
                    ->format('Y-m-d H:i:s');
            }

            return Carbon::parse(trim((string) $value))->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
