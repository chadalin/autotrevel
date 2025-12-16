<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VersionCommand extends Command
{
    protected $signature = 'version {action?} {--bump=patch : Увеличить версию (major, minor, patch)}';
    protected $description = 'Управление версией приложения';

   // php artisan version bump
   //'git' is not recognized as an internal or external command,
   // operable program or batch file.
   // Git тег создан: v1.1.5
   // Версия обновлена: 1.1.4 → 1.1.5
    
    
    public function handle()
    {
        $action = $this->argument('action') ?: 'show';

        switch ($action) {
            case 'set':
                return $this->setVersion();
            case 'bump':
                return $this->bumpVersion();
            default:
                return $this->showVersion();
        }
    }

    protected function showVersion()
    {
        $version = $this->getCurrentVersion();
        $this->info("Текущая версия: $version");
        return 0;
    }

    protected function getCurrentVersion()
    {
        // Пытаемся получить версию из файла
        $versionFile = base_path('VERSION');
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        
        // Возвращаем дефолтную версию
        return '1.0.0';
    }

    protected function setVersion()
    {
        $version = $this->ask('Введите новую версию (формат X.Y.Z)');
        
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            $this->error('Неверный формат версии. Используйте X.Y.Z');
            return 1;
        }

        $this->updateVersionFile($version);
        $this->createGitTag($version);
        
        $this->info("Версия установлена: $version");
        return 0;
    }

    protected function bumpVersion()
    {
        $current = $this->getCurrentVersion();
        $parts = explode('.', $current);
        $type = $this->option('bump');

        switch ($type) {
            case 'major':
                $parts[0] = (int)$parts[0] + 1;
                $parts[1] = 0;
                $parts[2] = 0;
                break;
            case 'minor':
                $parts[1] = (int)$parts[1] + 1;
                $parts[2] = 0;
                break;
            case 'patch':
                $parts[2] = (int)$parts[2] + 1;
                break;
            default:
                $this->error('Неверный тип обновления. Используйте major, minor или patch');
                return 1;
        }

        $newVersion = implode('.', $parts);
        $this->updateVersionFile($newVersion);
        $this->createGitTag($newVersion);
        
        $this->info("Версия обновлена: $current → $newVersion");
        return 0;
    }

    protected function updateVersionFile($version)
    {
        $versionFile = base_path('VERSION');
        file_put_contents($versionFile, $version);
        
        // Очистка кеша без зависимости от фасада
        $this->clearVersionCache();
    }

    protected function clearVersionCache()
    {
        // Если приложение загружено - очищаем кеш
        if (function_exists('app') && app()->bound('cache')) {
            try {
                app('cache')->forget('app.version');
            } catch (\Exception $e) {
                // Игнорируем ошибки кеша
            }
        }
    }

    protected function createGitTag($version)
    {
        $gitDir = base_path('.git');
        if (is_dir($gitDir)) {
            $tag = "v$version";
            shell_exec("git tag -a $tag -m 'Версия $version'");
            $this->info("Git тег создан: $tag");
        }
    }
}