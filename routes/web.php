<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// HR Dashboard - protected by auth
Route::middleware(['auth'])->group(function () {
    Route::get('/hr-dashboard', function () {
        return view('hr-dashboard');
    })->name('hr.dashboard');
    
    Route::get('/employee/dashboard', function () {
        return view('hr-dashboard');
    })->name('employee.dashboard');
});

// Redirect after login based on role
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('admin')) {
        return redirect('/admin');
    }
    return redirect('/hr-dashboard');
})->middleware(['auth'])->name('dashboard');
use App\Http\Controllers\ReportsController;
Route::middleware(['auth:web'])->group(function () {
    Route::get('/reports/employees/csv',  [ReportsController::class, 'employeesCsv'])->name('reports.employees.csv');
    Route::get('/reports/payroll/csv',    [ReportsController::class, 'payrollCsv'])->name('reports.payroll.csv');
    Route::get('/reports/attendance/csv', [ReportsController::class, 'attendanceCsv'])->name('reports.attendance.csv');
    Route::get('/payslip/{id}',           [ReportsController::class, 'payslip'])->name('payslip.employee');
});


// Setup Wizard
Route::get("/setup", [App\Http\Controllers\SetupController::class, "index"])->name("setup.index");
Route::post("/setup", [App\Http\Controllers\SetupController::class, "store"])->name("setup.store");
Route::get("/setup/complete", [App\Http\Controllers\SetupController::class, "complete"])->name("setup.complete");
