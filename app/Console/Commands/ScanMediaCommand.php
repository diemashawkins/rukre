<?php

namespace App\Console\Commands;

use App\Media\MediaScanner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('rukre:scan {--force : Re-read metadata and artwork for every file, even unchanged ones}')]
#[Description('Index the videos, audio and books on the home server')]
class ScanMediaCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MediaScanner $scanner): int
    {
        $stats = $scanner->scan(
            force: (bool) $this->option('force'),
            report: fn (string $message) => $this->line($message),
        );

        $this->newLine();
        $this->table(['Added', 'Updated', 'Unchanged', 'Removed', 'Failed'], [array_values($stats)]);

        return self::SUCCESS;
    }
}
