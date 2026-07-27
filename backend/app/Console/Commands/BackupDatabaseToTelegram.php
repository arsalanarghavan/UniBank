<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Modules\Telegram\Services\TelegramPublisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupDatabaseToTelegram extends Command
{
    protected $signature = 'ostadbank:backup-db';

    protected $description = 'Dump database and send backup to Telegram backup channel';

    public function handle(TelegramPublisher $publisher): int
    {
        $backupChat = Setting::getValue('backup_channel_id', config('services.telegram.backup_channel_id'));
        if (! $backupChat) {
            $this->warn('No backup channel configured');

            return self::SUCCESS;
        }

        $path = storage_path('app/backups');
        File::ensureDirectoryExists($path);
        $file = $path.'/backup-'.now()->format('Ymd-His').'.sql';

        $connection = config('database.default');
        $db = config("database.connections.{$connection}");

        if (($db['driver'] ?? '') === 'sqlite') {
            File::copy($db['database'], $file.'.sqlite');
            $file = $file.'.sqlite';
        } else {
            $cmd = sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s > %s',
                escapeshellarg($db['host']),
                escapeshellarg((string) $db['port']),
                escapeshellarg($db['username']),
                escapeshellarg($db['password']),
                escapeshellarg($db['database']),
                escapeshellarg($file),
            );
            Process::run($cmd);
        }

        $publisher->sendMessage($backupChat, 'DB backup created: '.basename($file));

        return self::SUCCESS;
    }
}
