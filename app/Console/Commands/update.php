<?php

namespace App\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;
use InvalidArgumentException;
use RuntimeException;

class update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update
        {--user= : The user that PHP runs under. All files will be owned by this user.}
        {--group= : The group that PHP runs under. All files will be owned by this group.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update your Dashboard to the latest version';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->output->warning('This command does just pull the newest changes from the github repo. Verify the github repo before running this');

        if (version_compare(PHP_VERSION, '8.2.0') < 0) {
            $this->error('Cannot execute self-upgrade process. The minimum required PHP version required is 8.2.0, you have ['.PHP_VERSION.'].');
            return Command::FAILURE;
        }

        $user = 'www-data';
        $group = 'www-data';
        if (is_null($this->option('user'))) {
            $userDetails = posix_getpwuid(fileowner('public'));
            $user = $userDetails['name'] ?? 'www-data';

            if ($this->input->isInteractive() && ! $this->confirm("Your webserver user has been detected as [{$user}]: is this correct?", true)) {
                $user = $this->anticipate(
                    'Please enter the name of the user running your webserver process. This varies from system to system, but is generally "www-data", "nginx", or "apache".',
                    ['www-data', 'nginx', 'apache']
                );
            }
        }

        if (is_null($this->option('group'))) {
            $groupDetails = posix_getgrgid(filegroup('public'));
            $group = $groupDetails['name'] ?? 'www-data';

            if ($this->input->isInteractive() && ! $this->confirm("Your webserver group has been detected as [{$group}]: is this correct?", true)) {
                $group = $this->anticipate(
                    'Please enter the name of the group running your webserver process. Normally this is the same as your user.',
                    ['www-data', 'nginx', 'apache']
                );
            }
        }

        $user = $this->validateOwnershipIdentifier($this->option('user') ?? $user, 'user');
        $group = $this->validateOwnershipIdentifier($this->option('group') ?? $group, 'group');

        ini_set('output_buffering', 0);

        if ($this->input->isInteractive() && ! $this->confirm('Are you sure you want to run the upgrade process for your Dashboard?')) {
            return Command::INVALID;
        }

        $bar = $this->output->createProgressBar(8);
        $bar->start();

        $maintenanceEnabled = false;

        try {
            $this->withProgress($bar, function () {
                $this->line('$upgrader> git pull');
                $this->runProcessOrFail(Process::fromShellCommandline('git pull'));
            });

            $this->withProgress($bar, function () use (&$maintenanceEnabled) {
                $this->line('$upgrader> php artisan down');
                if ($this->call('down') !== Command::SUCCESS) {
                    throw new RuntimeException('Failed to enable maintenance mode.');
                }

                $maintenanceEnabled = true;
            });

            $this->withProgress($bar, function () {
                $this->line('$upgrader> chmod -R 755 storage bootstrap/cache');
                $this->runProcessOrFail(new Process(['chmod', '-R', '755', 'storage', 'bootstrap/cache']));
            });

            $this->withProgress($bar, function () {
                $command = ['composer', 'install', '--no-ansi'];
                if (config('app.env') === 'production' && ! config('app.debug')) {
                    $command[] = '--optimize-autoloader';
                    $command[] = '--no-dev';
                }

                $this->line('$upgrader> '.implode(' ', $command));
                $process = new Process($command);
                $process->setTimeout(10 * 60);
                $this->runProcessOrFail($process);
            });

            $this->withProgress($bar, function () {
                $this->line('$upgrader> php artisan view:clear');
                if ($this->call('view:clear') !== Command::SUCCESS) {
                    throw new RuntimeException('Failed to clear compiled views.');
                }
            });

            $this->withProgress($bar, function () {
                $this->line('$upgrader> php artisan config:clear');
                if ($this->call('config:clear') !== Command::SUCCESS) {
                    throw new RuntimeException('Failed to clear config cache.');
                }
            });

            $this->withProgress($bar, function () {
                $this->line('$upgrader> php artisan migrate --force');
                if ($this->call('migrate', ['--force' => '']) !== Command::SUCCESS) {
                    throw new RuntimeException('Database migrations failed.');
                }
            });

            $this->withProgress($bar, function () use ($user, $group) {
                $this->line("\$upgrader> chown -R {$user}:{$group} storage bootstrap/cache");
                $process = new Process(['chown', '-R', "{$user}:{$group}", 'storage', 'bootstrap/cache'], $this->getLaravel()->basePath());
                $process->setTimeout(10 * 60);
                $this->runProcessOrFail($process);
            });
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            if ($maintenanceEnabled) {
                $this->call('up');
            }

            return Command::FAILURE;
        }

        if ($maintenanceEnabled) {
            $this->line('$upgrader> php artisan up');
            $this->call('up');
        }

        $this->newLine();
        $this->info('Finished running upgrade.');

        return Command::SUCCESS;
    }

    protected function withProgress(ProgressBar $bar, Closure $callback)
    {
        $bar->clear();
        $callback();
        $bar->advance();
        $bar->display();
    }

    private function runProcessOrFail(Process $process): void
    {
        $process->run(function ($type, $buffer) {
            $this->{$type === Process::ERR ? 'error' : 'line'}($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput() ?: $process->getOutput() ?: 'Command execution failed.');
        }
    }

    private function validateOwnershipIdentifier(string $value, string $label): string
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $value)) {
            throw new InvalidArgumentException("Invalid {$label} value supplied.");
        }

        return $value;
    }
}
