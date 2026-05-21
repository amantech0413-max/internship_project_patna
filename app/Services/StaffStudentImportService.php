<?php

namespace App\Services;

use App\Imports\StudentImport;
use App\Models\ExcelImportLog;
use App\Repositories\Contracts\StaffStudentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StaffStudentImportService
{
    public function __construct(protected StaffStudentRepositoryInterface $students) {}

    /**
     * @return array{import_token: string, rows: array<int, array>, invalid_count: int, total: int}
     */
    public function previewExcel(UploadedFile $file): array
    {
        $parsed = $this->parseRows(Excel::toCollection(new StudentImport, $file)->first() ?? collect());
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

        $token = (string) Str::uuid();
        Cache::put($this->cacheKey($token), [
            'rows' => array_values(array_filter($rows, fn ($r) => $r['valid'])),
            'file_name' => $file->getClientOriginalName(),
        ], now()->addHour());

        return [
            'import_token' => $token,
            'rows' => $rows,
            'invalid_count' => $invalid,
            'total' => count($rows),
        ];
    }

    public function confirmImport(int $collegeId, string $importToken, int $importedBy, ?array $rowsOverride = null): array
    {
        $cached = Cache::get($this->cacheKey($importToken), []);
        $rows = $rowsOverride ?? ($cached['rows'] ?? []);
        $fileName = $cached['file_name'] ?? null;

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
                $errors[] = ['row' => $row['row'] ?? $index + 2, 'message' => $validation['error']];

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
                $errors[] = ['row' => $row['row'] ?? $index + 2, 'message' => $e->getMessage()];
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

        Cache::forget($this->cacheKey($importToken));

        return [
            'success_count' => $success,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
            'errors' => $errors,
        ];
    }

    protected function parseRows(Collection $rows): array
    {
        $parsed = [];
        $line = 0;

        foreach ($rows as $row) {
            $line++;
            $name = trim((string) ($row['student_name'] ?? $row['student name'] ?? $row[0] ?? ''));
            $mobile = preg_replace('/\D/', '', (string) ($row['mobile_number'] ?? $row['mobile number'] ?? $row[1] ?? ''));

            if ($name === '' && $mobile === '') {
                continue;
            }

            $parsed[] = [
                'row' => $line + 1,
                'student_name' => $name,
                'mobile_number' => $mobile,
            ];
        }

        return $parsed;
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

    protected function cacheKey(string $token): string
    {
        return 'staff_student_import:'.$token;
    }
}
