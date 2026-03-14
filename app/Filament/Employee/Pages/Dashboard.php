<?php

namespace App\Filament\Employee\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected string $view = 'filament.employee.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Employee\Widgets\StatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}