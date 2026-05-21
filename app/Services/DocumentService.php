<?php

namespace App\Services;

use App\Jobs\GenerateCertificateJob;
use App\Jobs\GenerateOfferLetterJob;
use App\Models\Certificate;
use App\Models\OfferLetter;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentService
{
    public function uploadCertificate(Student $student, UploadedFile $file, int $adminId): Certificate
    {
        return Certificate::create([
            'student_id' => $student->id,
            'file_path' => $file->store('certificates', 'public'),
            'certificate_no' => 'CERT-' . strtoupper(Str::random(8)),
            'issued_date' => now()->toDateString(),
            'issued_by' => $adminId,
            'auto_generated' => false,
        ]);
    }

    public function queueAutoCertificate(Student $student, int $adminId): void
    {
        GenerateCertificateJob::dispatch($student->id, $adminId);
    }

    public function queueAutoOfferLetter(Student $student, int $adminId): void
    {
        GenerateOfferLetterJob::dispatch($student->id, $adminId);
    }

    public function generateOfferLetterPdf(Student $student): string
    {
        $pdf = Pdf::loadView('documents.offer-letter', [
            'student' => $student,
            'organization' => config('bli.organization'),
        ]);

        $path = 'offer_letters/' . $student->student_code . '_' . time() . '.pdf';
        $fullPath = storage_path('app/public/' . $path);
        @mkdir(dirname($fullPath), 0755, true);
        $pdf->save($fullPath);

        OfferLetter::create([
            'student_id' => $student->id,
            'file_path' => $path,
            'letter_no' => 'OL-' . $student->student_code,
            'issued_date' => now()->toDateString(),
            'auto_generated' => true,
        ]);

        return $path;
    }

    public function generateCertificatePdf(Student $student): string
    {
        $pdf = Pdf::loadView('documents.certificate', [
            'student' => $student,
            'organization' => config('bli.organization'),
        ]);

        $path = 'certificates/' . $student->student_code . '_' . time() . '.pdf';
        $fullPath = storage_path('app/public/' . $path);
        @mkdir(dirname($fullPath), 0755, true);
        $pdf->save($fullPath);

        Certificate::create([
            'student_id' => $student->id,
            'file_path' => $path,
            'certificate_no' => 'CERT-' . $student->student_code,
            'issued_date' => now()->toDateString(),
            'auto_generated' => true,
        ]);

        return $path;
    }
}
