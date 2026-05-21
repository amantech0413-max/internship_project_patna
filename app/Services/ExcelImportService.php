<?php

namespace App\Services;

use App\Models\ExcelImportLog;
use App\Repositories\Contracts\StaffStudentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class ExcelImportService
{
    public function __construct(protected StaffStudentRepositoryInterface $students) {}

    /**
     * @return array{rows: array<int, array{row: int, student_name: string, mobile_number: string, valid: bool, error?: string}>, invalid_count: int}
     */
    public function preview(UploadedFile $file): array
    {
        $parsed = $this->parseFile($file);
        $rows = [];
        $invalid = 0;

        foreach ($parsed as $item) {
            $validation = $this->validateRow($item['student_name'], $item['mobile_number']);
            if (! $validation['valid']) {
                $invalid++;
            }
            $rows[] = [
                'row' => $item['row'],
                'student_name' => $item['student_name'],
                'mobile_number' => $item['mobile_number'],
                'valid' => $validation['valid'],
                'error' => $validation['error'] ?? null,
            ];
        }

        return ['rows' => $rows, 'invalid_count' => $invalid];
    }

    /**
     * @param  array<int, array{student_name: string, mobile_number: string}>  $rows
     */
    public function import(
        int $collegeId,
        array $rows,
        int $importedBy,
        ?string $fileName = null
    ): array {
        $success = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $studentName = trim((string) ($row['student_name'] ?? ''));
            $mobile = preg_replace('/\D/', '', (string) ($row['mobile_number'] ?? ''));

            if ($studentName === '' && $mobile === '') {
                $skipped++;

                continue;
            }

            $validation = $this->validateRow($studentName, $mobile);
            if (! $validation['valid']) {
                $failed++;
                $errors[] = ['row' => $index + 2, 'message' => $validation['error']];

                continue;
            }

            try {
                $this->students->create([
                    'college_id' => $collegeId,
                    'student_name' => $studentName,
                    'mobile_number' => $mobile,
                    'created_by' => $importedBy,
                    'status' => 'approved',
                ]);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['row' => $index + 2, 'message' => $e->getMessage()];
            }
        }

        ExcelImportLog::create([
            'college_id' => $collegeId,
            'imported_by' => $importedBy,
            'file_name' => $fileName,
            'total_rows' => count($rows),
            'success_count' => $success,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
            'errors' => $errors,
        ]);

        return [
            'total_rows' => count($rows),
            'success_count' => $success,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @return array<int, array{row: int, student_name: string, mobile_number: string}>
     */
    protected function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'])) {
            return $this->parseCsv($file->getRealPath());
        }

        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->parseXlsx($file->getRealPath());
        }

        return [];
    }

    /**
     * @return array<int, array{row: int, student_name: string, mobile_number: string}>
     */
    protected function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if (! $handle) {
            return [];
        }

        $line = 0;
        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            if ($line === 1 && $this->isHeaderRow($data)) {
                continue;
            }
            $rows[] = [
                'row' => $line,
                'student_name' => trim((string) ($data[0] ?? '')),
                'mobile_number' => preg_replace('/\D/', '', (string) ($data[1] ?? '')),
            ];
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array{row: int, student_name: string, mobile_number: string}>
     */
    protected function parseXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('PHP Zip extension is required for Excel import.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Unable to read Excel file.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si as $si) {
                $sharedStrings[] = trim((string) ($si->t ?? $si->r->t ?? ''));
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! $sheetXml) {
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $sheet->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];
        $line = 0;

        foreach ($sheet->sheetData->row as $row) {
            $line++;
            $cells = [];
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $col = preg_replace('/\d+/', '', $ref);
                $value = '';

                if (isset($cell['t']) && (string) $cell['t'] === 's') {
                    $idx = (int) $cell->v;
                    $value = $sharedStrings[$idx] ?? '';
                } else {
                    $value = trim((string) ($cell->v ?? ''));
                }

                $cells[$col] = $value;
            }

            $name = $cells['A'] ?? '';
            $mobile = preg_replace('/\D/', '', $cells['B'] ?? '');

            if ($line === 1 && $this->isHeaderRow([$name, $mobile])) {
                continue;
            }

            if ($name === '' && $mobile === '') {
                continue;
            }

            $rows[] = [
                'row' => $line,
                'student_name' => $name,
                'mobile_number' => $mobile,
            ];
        }

        return $rows;
    }

    protected function isHeaderRow(array $cells): bool
    {
        $first = strtolower(trim((string) ($cells[0] ?? '')));

        return str_contains($first, 'student') || str_contains($first, 'name');
    }

    /**
     * @return array{valid: bool, error?: string}
     */
    protected function validateRow(string $studentName, string $mobile): array
    {
        if ($studentName === '') {
            return ['valid' => false, 'error' => 'Student name is required.'];
        }

        if (! preg_match('/^\d{10}$/', $mobile)) {
            return ['valid' => false, 'error' => 'Mobile must be 10 digits.'];
        }

        return ['valid' => true];
    }
}
