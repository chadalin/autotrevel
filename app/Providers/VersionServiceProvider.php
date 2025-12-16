<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class VersionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Регистрируем директиву Blade для отображения версии
        Blade::directive('version', function () {
            return "<?php echo app('version'); ?>";
        });
        
        Blade::directive('versionBadge', function () {
            return "<?php echo '<span class=\"version-badge\">v' . app('version') . '</span>'; ?>";
        });
    }

    public function register()
    {
        // Регистрируем singleton для получения версии
        $this->app->singleton('version', function () {
            return Cache::remember('app.version', 3600, function () {
                $versionFile = base_path('VERSION');
                
                if (File::exists($versionFile)) {
                    return trim(File::get($versionFile));
                }
                
                return '1.0.0';
            });
        });
    }
}