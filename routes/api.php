<?php

use App\Http\Controllers\Api\V1\Admin\CollegeController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ExcelImportLogController;
use App\Http\Controllers\Api\V1\Admin\InternshipGroupController;
use App\Http\Controllers\Api\V1\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\StaffStudentController;
use App\Http\Controllers\Api\V1\Admin\StaffUserController;
use App\Http\Controllers\Api\V1\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Api\V1\Admin\WhatsAppController;
use App\Http\Controllers\Api\V1\Admin\WhatsappMessageController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PublicRegistrationController;
use App\Http\Controllers\Api\V1\Admin\BinController;
use App\Http\Controllers\Api\V1\Staff\StaffStudentImportController;
use App\Http\Controllers\Api\V1\StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('registration/colleges', [PublicRegistrationController::class, 'colleges']);
    Route::get('registration/colleges/{slug}', [PublicRegistrationController::class, 'collegeBySlug']);

    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::get('access', [AuthController::class, 'access']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('student')->middleware(['role:student', 'student.approved'])->group(function () {
            Route::get('profile', [StudentDashboardController::class, 'profile']);
            Route::put('profile', [StudentDashboardController::class, 'updateProfile']);
            Route::post('profile/update', [StudentDashboardController::class, 'updateProfile']);
            Route::get('internship', [StudentDashboardController::class, 'internshipDetails']);
            Route::get('group', [StudentDashboardController::class, 'assignedGroup']);
            Route::get('whatsapp', [StudentDashboardController::class, 'whatsappLink']);
            Route::get('documents', [StudentDashboardController::class, 'documents']);
            Route::get('daily-reports', [StudentDashboardController::class, 'dailyReports']);
            Route::post('daily-reports', [StudentDashboardController::class, 'submitDailyReport']);
            Route::get('assignments', [StudentDashboardController::class, 'assignments']);
            Route::post('assignments', [StudentDashboardController::class, 'submitAssignment']);
            Route::get('notifications', [StudentDashboardController::class, 'notifications']);
            Route::post('attendance', [StudentDashboardController::class, 'markAttendance']);
        });

        Route::prefix('admin')->middleware('role:super_admin,admin,college_coordinator')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index']);

            // Roles & permissions (super_admin only)
            Route::middleware('role:super_admin')->group(function () {
                Route::get('roles/permissions', [RoleController::class, 'permissions']);
                Route::get('roles/assignable', [RoleController::class, 'assignable']);
                Route::apiResource('roles', RoleController::class)->except(['create', 'edit']);
            });

            // Staff user management (staff_manage permission)
            Route::middleware('permission:staff_manage')->group(function () {
                Route::get('staff-users/permission-keys', [StaffUserController::class, 'permissionKeys']);
                Route::get('staff-users/roles', [RoleController::class, 'assignable']);
                Route::apiResource('staff-users', StaffUserController::class)->except(['create', 'edit']);
            });

            // College dropdown for entry + college modules
            Route::get('colleges/dropdown', [CollegeController::class, 'dropdown'])
                ->middleware('permission:staff_entry,college_manage');

            Route::middleware('permission:college_manage')->group(function () {
                Route::apiResource('colleges', CollegeController::class)->except(['create', 'edit']);
            });

            // Quick staff entry + import logs
            Route::middleware('permission:staff_entry')->group(function () {
                Route::get('staff-students', [StaffStudentController::class, 'index']);
                Route::post('staff-students', [StaffStudentController::class, 'store']);
                Route::get('import-logs', [ExcelImportLogController::class, 'index']);
            });

            // Full internship student management
            Route::middleware('permission:student_view')->group(function () {
                Route::get('students', [AdminStudentController::class, 'index']);
                Route::get('students/export', [AdminStudentController::class, 'exportCsv']);
                Route::get('students/mobile/{mobile}', [AdminStudentController::class, 'byMobile']);
                Route::get('students/{id}', [AdminStudentController::class, 'show']);
            });

            Route::middleware('permission:student_create')->group(function () {
                Route::post('students', [AdminStudentController::class, 'store']);
            });

            Route::middleware('permission:student_edit')->group(function () {
                Route::put('students/{id}', [AdminStudentController::class, 'update']);
                Route::patch('students/{id}', [AdminStudentController::class, 'update']);
                Route::post('students/{id}/update', [AdminStudentController::class, 'update']);
                Route::post('students/{id}/offer-letter', [AdminStudentController::class, 'generateOfferLetter']);
                Route::post('students/{id}/certificate', [AdminStudentController::class, 'generateCertificate']);
                Route::post('students/{id}/upload-certificate', [AdminStudentController::class, 'uploadCertificate']);
            });

            Route::middleware('permission:student_approve')->group(function () {
                Route::post('students/{id}/approve', [AdminStudentController::class, 'approve']);
                Route::post('students/{id}/reject', [AdminStudentController::class, 'reject']);
            });

            Route::middleware('permission:student_delete')->group(function () {
                Route::delete('students/{id}', [AdminStudentController::class, 'destroy']);
            });

            Route::middleware('permission:bin_delete_permanent')->group(function () {
                Route::delete('students/{id}/force', [AdminStudentController::class, 'forceDestroy']);
            });

            Route::middleware('permission:bin_manage,bin_delete_permanent')->group(function () {
                Route::get('bin', [BinController::class, 'index']);
            });

            Route::middleware('permission:bin_manage')->group(function () {
                Route::post('bin/restore', [BinController::class, 'restore']);
            });

            Route::middleware('permission:bin_delete_permanent')->group(function () {
                Route::delete('bin/force', [BinController::class, 'forceDestroy']);
            });

            // Internship groups, WhatsApp, notifications (admin / full staff)
            Route::post('whatsapp/preview-students', [WhatsAppController::class, 'previewStudents']);
            Route::post('whatsapp/send-messages', [WhatsAppController::class, 'sendMessages']);

            Route::apiResource('groups', InternshipGroupController::class);
            Route::post('groups/{group}/assign', [InternshipGroupController::class, 'assignStudents']);
            Route::post('groups/{group}/unassign', [InternshipGroupController::class, 'unassignStudents']);
            Route::get('groups/{group}/available-students', [InternshipGroupController::class, 'availableStudents']);
            Route::post('groups/{group}/whatsapp/send', [InternshipGroupController::class, 'sendWhatsappInvitations']);

            Route::get('whatsapp/messages', [WhatsappMessageController::class, 'index']);
            Route::post('whatsapp/messages/retry-failed', [WhatsappMessageController::class, 'retryFailed']);
            Route::post('whatsapp/messages/{whatsappMessage}/resend', [WhatsappMessageController::class, 'resend']);

            Route::get('notifications', [AdminNotificationController::class, 'index']);
            Route::post('notifications/broadcast', [AdminNotificationController::class, 'broadcast']);
        });

        Route::prefix('staff')->middleware(['role:super_admin,admin,college_coordinator', 'permission:staff_entry'])->group(function () {
            Route::prefix('student/import')->group(function () {
                Route::get('sample', [StaffStudentImportController::class, 'downloadSample']);
                Route::post('upload', [StaffStudentImportController::class, 'uploadExcel']);
                Route::post('confirm', [StaffStudentImportController::class, 'confirmImport']);
            });
        });
    });
});
