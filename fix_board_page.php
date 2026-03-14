<?php
$blade = <<<'BLADE'
<x-filament-panels::page class="!p-0 !overflow-hidden">
<style>
[x-data*="flowforge"] {
    display: flex !important;
    flex-direction: column !important;
    height: calc(100vh - 9rem) !important;
}
[x-data*="flowforge"] > div:nth-child(2) {
    flex: 1 !important;
    overflow: hidden !important;
}
[x-data*="flowforge"] > div:nth-child(2) > div {
    display: flex !important;
    flex-direction: row !important;
    gap: 14px !important;
    height: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    align-items: flex-start !important;
    padding: 2px 2px 16px 2px !important;
}
.flowforge-column {
    min-width: 270px !important;
    max-width: 270px !important;
    flex-shrink: 0 !important;
}
.flowforge-column-content {
    max-height: calc(100vh - 15rem) !important;
    overflow-y: auto !important;
}
.flowforge-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
}
.flowforge-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(0,0,0,0.35) !important;
}
.dark .flowforge-column {
    background: rgba(15,23,42,0.7) !important;
    border-color: rgba(255,255,255,0.08) !important;
}
.dark .flowforge-card {
    background: #1e293b !important;
    border-color: rgba(255,255,255,0.08) !important;
}
.dark .flowforge-column-header {
    background: rgba(30,41,59,0.9) !important;
    border-color: rgba(255,255,255,0.08) !important;
}
::-webkit-scrollbar { height: 5px; width: 5px; }
::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
</style>
    <div class="h-[calc(100vh-8rem)] overflow-hidden">
        {{ $this->board }}
    </div>
</x-filament-panels::page>
BLADE;

file_put_contents('resources/views/vendor/flowforge/filament/pages/board-page.blade.php', $blade);
echo "Written successfully\n";
echo "Run: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
