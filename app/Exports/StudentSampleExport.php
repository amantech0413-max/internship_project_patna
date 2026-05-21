<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Aman Kumar', '9876543210'],
            ['Rahul Sharma', '9123456780'],
        ];
    }

    public function headings(): array
    {
        return ['Student Name', 'Mobile Number'];
    }
}
