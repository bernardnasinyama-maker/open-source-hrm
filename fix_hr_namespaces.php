<?php

$baseDir = 'app/Filament/Employee/Resources';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$fixed = 0;
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;

    $content = file_get_contents($file->getPathname());
    $original = $content;

    // Fix all namespaces and use statements
    $content = str_replace(
        'namespace App\Filament\Resources\\',
        'namespace App\Filament\Employee\Resources\\',
        $content
    );
    $content = str_replace(
        'use App\Filament\Resources\\',
        'use App\Filament\Employee\Resources\\',
        $content
    );

    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $fixed++;
        echo "Fixed: " . basename($file->getPathname()) . "\n";
    }
}

echo "\nTotal fixed: $fixed files\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
