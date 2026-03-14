<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class SettingsPage extends Page
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 99;
    protected static ?string $slug = 'settings';

    public string $company_name       = 'CRBC Uganda';
    public string $project_name       = 'Design & Build of Kayunga-Bbaale-Galiraya Road (87KM)';
    public string $hr_email           = '';
    public string $currency           = 'UGX';
    public string $timezone           = 'Africa/Kampala';
    public string $mail_mailer        = 'log';
    public ?string $mail_host          = '';
    public string $mail_port          = '587';
    public ?string $mail_username      = '';
    public string $mail_password      = '';
    public ?string $mail_from_address  = '';
    public string $mail_from_name     = 'CRBC Uganda HRM';
    public bool   $notifications_enabled = true;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function mount(): void
    {
        // Load current .env values
        $this->mail_mailer       = env('MAIL_MAILER', 'log');
        $this->mail_host         = env('MAIL_HOST', '');
        $this->mail_port         = env('MAIL_PORT', '587');
        $this->mail_username     = env('MAIL_USERNAME', '');
        $this->mail_from_address = env('MAIL_FROM_ADDRESS', '');
        $this->mail_from_name    = env('MAIL_FROM_NAME', 'CRBC Uganda HRM');
    }

    public function saveEmailSettings(): void
    {
        $this->updateEnv([
            'MAIL_MAILER'       => $this->mail_mailer,
            'MAIL_HOST'         => $this->mail_host,
            'MAIL_PORT'         => $this->mail_port,
            'MAIL_USERNAME'     => $this->mail_username,
            'MAIL_PASSWORD'     => $this->mail_password ?: env('MAIL_PASSWORD', ''),
            'MAIL_FROM_ADDRESS' => $this->mail_from_address,
            'MAIL_FROM_NAME'    => '"' . $this->mail_from_name . '"',
            'MAIL_ENCRYPTION'   => 'tls',
        ]);

        Artisan::call('optimize:clear');

        Notification::make()
            ->title('Email settings saved!')
            ->body('Mail configuration updated. Test by approving a leave request.')
            ->success()
            ->send();
    }

    public function clearCache(): void
    {
        Artisan::call('optimize:clear');
        Notification::make()->title('Cache cleared!')->success()->send();
    }

    public function clearLogs(): void
    {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        Notification::make()->title('Logs cleared!')->success()->send();
    }

    private function updateEnv(array $values): void
    {
        $envFile = base_path('.env');
        $content = file_get_contents($envFile);

        foreach ($values as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $content);
    }
}
