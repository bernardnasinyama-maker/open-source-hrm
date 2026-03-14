<?php

$resources = [
    'Correspondences' => 'CorrespondenceResource',
    'Expenses'        => 'SiteExpenseResource',
    'Disciplinary'    => 'DisciplinaryResource',
    'Departments'     => 'DepartmentResource',
];

foreach ($resources as $folder => $resourceClass) {
    $srcDir  = "app/Filament/Resources/{$folder}";
    $dstDir  = "app/Filament/Employee/Resources/{$folder}";

    if (!is_dir($srcDir)) {
        echo "SKIP: $srcDir not found\n";
        continue;
    }

    // Recursively copy all files including Pages/
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $dstPath = $dstDir . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
        if ($item->isDir()) {
            if (!is_dir($dstPath)) mkdir($dstPath, 0755, true);
        } else {
            $content = file_get_contents($item->getPathname());
            // Fix namespace
            $content = str_replace(
                'namespace App\Filament\Resources\\',
                'namespace App\Filament\Employee\Resources\\',
                $content
            );
            // Fix use statements pointing to admin resources
            $content = str_replace(
                'use App\Filament\Resources\\',
                'use App\Filament\Employee\Resources\\',
                $content
            );
            file_put_contents($dstPath, $content);
        }
    }
    echo "✅ $folder copied with Pages\n";
}

// Verify
echo "\nFiles created:\n";
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('app/Filament/Employee/Resources', RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter as $file) {
    if ($file->getExtension() === 'php') {
        echo "  " . str_replace('app/Filament/Employee/Resources/', '', $file->getPathname()) . "\n";
    }
}

echo "\nDone! Run: php artisan optimize:clear\n";
