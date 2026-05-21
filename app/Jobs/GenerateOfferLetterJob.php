<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\DocumentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateOfferLetterJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $studentId,
        public int $adminId
    ) {}

    public function handle(DocumentService $documents): void
    {
        $student = Student::find($this->studentId);

        if ($student) {
            $documents->generateOfferLetterPdf($student);
        }
    }
}
