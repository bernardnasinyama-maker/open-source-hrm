<?php

$routes = file_get_contents('routes/web.php');

// Check what's already there
echo "Current web.php:\n";
echo $routes . "\n\n";

// Add payslip route if missing
if (strpos($routes, 'payslip.employee') === false) {
    $routes .= '

// Payslip & Reports
Route::middleware(["auth:web"])->group(function () {
    Route::get("/payslip/{id}", [App\Http\Controllers\ReportsController::class, "payslip"])
        ->name("payslip.employee");
    Route::get("/reports/employees/csv", [App\Http\Controllers\ReportsController::class, "employeesCsv"])
        ->name("reports.employees.csv");
    Route::get("/reports/payroll/csv", [App\Http\Controllers\ReportsController::class, "payrollCsv"])
        ->name("reports.payroll.csv");
});
';
    file_put_contents('routes/web.php', $routes);
    echo "Routes added!\n";
} else {
    echo "Route already exists\n";
}
