<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Console\Command;

class CleanupOpenPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:open:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all payments from the database that have state "open"';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Stale open payments are marked canceled so late provider callbacks can still be reconciled.
        try {
            Payment::where('status', PaymentStatus::OPEN->value)
                ->where('updated_at', '<', now()->subHour())
                ->update(['status' => PaymentStatus::CANCELED->value]);
        } catch (\Exception $e) {
            $this->error('Could not delete payments: ' . $e->getMessage());
            return 1;
        }

        $this->info('Successfully marked stale open payments as canceled');
        return Command::SUCCESS;
    }
}
