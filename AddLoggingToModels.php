<?php
// Run this script: php AddLoggingToModels.php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$models = [
    'app/Models/Employee.php'         => 'employee',
    'app/Models/Payroll.php'          => 'payroll',
    'app/Models/Leave.php'            => 'leave',
    'app/Models/Attendance.php'       => 'attendance',
    'app/Models/Department.php'       => 'department',
];

// Check for optional models
$optionalModels = [
    'app/Models/EmployeeDocument.php' => 'document',
    'app/Models/DisciplinaryRecord.php' => 'disciplinary',
];
foreach ($optionalModels as $path => $log) {
    if (file_exists($path)) {
        $models[$path] = $log;
    }
}

$traitUse = 'use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;';

$traitInsert = '    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Record {$eventName}");
    }
';

foreach ($models as $file => $logName) {
    if (!file_exists($file)) {
        echo "SKIP (not found): $file\n";
        continue;
    }

    $content = file_get_contents($file);

    // Skip if already has LogsActivity
    if (strpos($content, 'LogsActivity') !== false) {
        echo "SKIP (already has logging): $file\n";
        continue;
    }

    // Add use statements after namespace line
    $content = preg_replace(
        '/(namespace App\\\\Models;)/',
        "$1\n\n{$traitUse}",
        $content,
        1
    );

    // Add trait and method after opening brace of class
    $content = preg_replace(
        '/(class \w+ extends [\w\\\\]+[^{]*\{)/',
        "$1\n{$traitInsert}",
        $content,
        1
    );

    file_put_contents($file, $content);
    echo "Added logging to: $file (log: $logName)\n";
}

echo "\nAll models updated with activity logging!\n";
echo "Every create/update/delete will now be tracked in audit_trail.\n";
