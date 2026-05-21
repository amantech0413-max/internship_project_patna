<?php

namespace App\Services;

use App\Models\College;
use App\Models\ExcelImportLog;
use App\Models\InternshipGroup;
use App\Models\Student;

class DashboardService
{
    public function adminStats(): array
    {
        return [
            'total_colleges' => College::count(),
            'active_colleges' => College::where('status', 'active')->count(),
            'staff_entries' => Student::whereNotNull('college_id')->count(),
            'imports_today' => ExcelImportLog::whereDate('created_at', today())->count(),
            'total_students' => Student::count(),
            'online_interns' => Student::where('internship_mode', 'online')->count(),
            'offline_interns' => Student::where('internship_mode', 'offline')->count(),
            'active_groups' => InternshipGroup::where('status', 'active')->count(),
            'completed_internships' => Student::where('status', 'completed')->count(),
            'pending_approvals' => Student::where('status', 'pending')->count(),
            'approved_students' => Student::where('status', 'approved')->count(),
        ];
    }
}
