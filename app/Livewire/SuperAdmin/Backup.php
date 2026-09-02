<?php

namespace App\Livewire\SuperAdmin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\Process\Process;
use ZipArchive;

#[Title('Backup')]
class Backup extends Component
{
    public function render()
    {
        return view('livewire.super-admin.backup');
    }

    public function download()
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);

        set_time_limit(0);

        $timestamp = now()->format('Y_m_d_His');
        $zipFilename = "dms_backup_{$timestamp}.zip";

        $tempDir = storage_path('app/temp/backup');
        if (! File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $sqlPath = $tempDir.DIRECTORY_SEPARATOR."database_{$timestamp}.sql";
        $zipPath = $tempDir.DIRECTORY_SEPARATOR.$zipFilename;

        try {
            $this->dumpDatabase($sqlPath);

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create backup archive.');
            }

            $zip->addFile($sqlPath, 'database/'.basename($sqlPath));

            $publicStoragePath = storage_path('app/public');
            if (File::isDirectory($publicStoragePath)) {
                $this->addDirectoryToZip($zip, $publicStoragePath, 'files');
            }

            $zip->close();

            activity()
                ->causedBy(Auth::user())
                ->log('Generated a system backup (database + files)');

            return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            $this->dispatch('error', message: 'Backup failed: '.$e->getMessage());

            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }
        } finally {
            if (File::exists($sqlPath)) {
                File::delete($sqlPath);
            }
        }
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $sourceDir, string $zipRootFolder): void
    {
        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = $zipRootFolder.'/'.ltrim(substr($filePath, strlen($sourceDir)), DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);

            $zip->addFile($filePath, $relativePath);
        }
    }

    protected function dumpDatabase(string $sqlPath): void
    {
        $connection = Config::get('database.default');
        $config = Config::get("database.connections.{$connection}");

        if ($connection === 'sqlite') {
            File::copy($config['database'], $sqlPath);

            return;
        }

        if ($connection === 'mysql' && $this->dumpMysqlViaBinary($config, $sqlPath)) {
            return;
        }

        $this->dumpMysqlViaPhp($sqlPath);
    }

    protected function dumpMysqlViaBinary(array $config, string $sqlPath): bool
    {
        $binary = $this->resolveMysqldumpBinary();
        if (! $binary) {
            return false;
        }

        $process = new Process([
            $binary,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--single-transaction',
            '--skip-lock-tables',
            '--result-file='.$sqlPath,
            $config['database'],
        ], null, [
            'MYSQL_PWD' => $config['password'],
        ]);

        $process->setTimeout(600);
        $process->run();

        return $process->isSuccessful() && File::exists($sqlPath) && File::size($sqlPath) > 0;
    }

    protected function resolveMysqldumpBinary(): ?string
    {
        $candidates = array_filter(array_merge(
            [env('MYSQLDUMP_PATH')],
            glob('C:/laragon/bin/mysql/*/bin/mysqldump.exe') ?: [],
            ['mysqldump']
        ));

        foreach ($candidates as $candidate) {
            $process = new Process([$candidate, '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                return $candidate;
            }
        }

        return null;
    }

    protected function dumpMysqlViaPhp(string $sqlPath): void
    {
        $pdo = DB::connection()->getPdo();

        $handle = fopen($sqlPath, 'w');
        fwrite($handle, "-- DMS Database Backup\n-- Generated: ".now()->toDateTimeString()."\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $createStmt['Create Table'].";\n\n");

            $rows = $pdo->query("SELECT * FROM `{$table}`");
            $rows->setFetchMode(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $columns = array_map(fn ($col) => "`{$col}`", array_keys($row));
                $values = array_map(function ($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    return $pdo->quote($value);
                }, array_values($row));

                fwrite($handle, "INSERT INTO `{$table}` (".implode(', ', $columns).') VALUES ('.implode(', ', $values).");\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }
}
