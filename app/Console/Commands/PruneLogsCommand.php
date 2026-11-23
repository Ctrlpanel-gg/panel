<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PruneLogsCommand extends Command
{
    protected $signature = 'logs:prune {--dry-run : Show what would be done without changing files}';

    protected $description = 'Compress old daily logs (.gz) and delete logs past retention.';

    public function handle(): int
    {
        $logDir = storage_path('logs');
        $keepDays = (int) config('logging.prune.keep_days', 14);
        $compressAfterDays = (int) config('logging.prune.compress_after_days', 2);
        $dryRun = (bool) $this->option('dry-run');

        if (! File::isDirectory($logDir)) {
            $this->warn("Log directory not found: {$logDir}");

            return Command::SUCCESS;
        }

        $deleteCutoff = Carbon::today()->subDays($keepDays);
        $compressCutoff = Carbon::today()->subDays($compressAfterDays);
        $compressed = 0;
        $deleted = 0;

        // Plain logs: compress when aging, delete past retention.
        foreach (File::glob($logDir . '/laravel*.log') ?: [] as $path) {
            if ($this->isActiveLog($path)) {
                continue;
            }

            $logDay = $this->logDay($path);

            if ($logDay->lt($deleteCutoff)) {
                $deleted++;
                $this->line(($dryRun ? '[dry-run] Would delete ' : 'Deleting ') . basename($path));
                if (! $dryRun) {
                    File::delete($path);
                }
                continue;
            }

            if ($logDay->lt($compressCutoff)) {
                if (File::size($path) === 0) {
                    $deleted++;
                    $this->line(($dryRun ? '[dry-run] Would delete empty ' : 'Deleting empty ') . basename($path));
                    if (! $dryRun) {
                        File::delete($path);
                    }
                    continue;
                }

                $target = $this->gzipTarget($path);
                $compressed++;
                $this->line(($dryRun ? '[dry-run] Would compress ' : 'Compressing ') . basename($path));
                if (! $dryRun) {
                    $this->gzipFile($path, $target);
                }
            }
        }

        // Compressed logs: delete past retention.
        foreach (File::glob($logDir . '/laravel*.log.gz') ?: [] as $path) {
            if ($this->logDay($path)->lt($deleteCutoff)) {
                $deleted++;
                $this->line(($dryRun ? '[dry-run] Would delete ' : 'Deleting ') . basename($path));
                if (! $dryRun) {
                    File::delete($path);
                }
            }
        }

        $this->info("Done. Compressed: {$compressed}, deleted: {$deleted}." . ($dryRun ? ' (dry-run)' : ''));

        return Command::SUCCESS;
    }

    /**
     * File Monolog is writing to today must never be touched.
     */
    private function isActiveLog(string $path): bool
    {
        return $this->logDay($path)->isToday();
    }

    /**
     * Date from filename; legacy dateless file falls back to mtime.
     */
    private function logDay(string $path): Carbon
    {
        if (preg_match('/laravel-(\d{4}-\d{2}-\d{2})\.log/', basename($path), $matches)) {
            return Carbon::parse($matches[1])->startOfDay();
        }

        return Carbon::createFromTimestamp(File::lastModified($path))->startOfDay();
    }

    /**
     * Dated files keep name + .gz; legacy file gets its mtime as date.
     */
    private function gzipTarget(string $path): string
    {
        if (basename($path) === 'laravel.log') {
            $date = date('Y-m-d', File::lastModified($path));

            return storage_path("logs/laravel-{$date}.log.gz");
        }

        return $path . '.gz';
    }

    /**
     * Stream through gzip so multi-GB files never load into memory.
     */
    private function gzipFile(string $source, string $target): void
    {
        $in = fopen($source, 'rb');
        $out = gzopen($target, 'wb9');
        if ($in === false || $out === false) {
            if (is_resource($in)) {
                fclose($in);
            }
            if ($out !== false) {
                gzclose($out);
            }

            return;
        }

        while (! feof($in)) {
            $chunk = fread($in, 1048576);
            if ($chunk === false) {
                break;
            }
            gzwrite($out, $chunk);
        }

        fclose($in);
        gzclose($out);
        touch($target, File::lastModified($source));
        File::delete($source);
    }
}
