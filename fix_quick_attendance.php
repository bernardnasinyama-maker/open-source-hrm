<?php

$files = [
    'app/Filament/Pages/QuickAttendance.php',
    'app/Filament/Employee/Pages/QuickAttendance.php',
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // Fix navigationIcon type
    $c = str_replace(
        'protected static ?string $navigationIcon = "heroicon-o-clock";',
        'protected static string|\\BackedEnum|null $navigationIcon = "heroicon-o-clock";',
        $c
    );
    // Fix navigationLabel type
    $c = str_replace(
        'protected static ?string $navigationLabel = "Quick Attendance";',
        'protected static string|\\UnitEnum|null $navigationLabel = "Quick Attendance";',
        $c
    );
    // Fix navigationGroup type
    $c = str_replace(
        'protected static ?string $navigationGroup = "HR Management";',
        'protected static string|\\UnitEnum|null $navigationGroup = "HR Management";',
        $c
    );
    // Fix title type
    $c = str_replace(
        'protected static ?string $title = "Quick Attendance";',
        'protected static string|\\BackedEnum|null $title = "Quick Attendance";',
        $c
    );

    file_put_contents($f, $c);
    echo "Fixed: $f\n";
}

echo "Done!\n";
