<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Http\Services\Email;

class AnalyzeLogs extends Command
{
    protected $signature = 'logs:analyze';
    protected $description = 'Analyze logs for anomalies';

    public function handle()
    {
        $logs = Storage::get('logs/laravel.log');
        $errors = substr_count($logs, 'error');
        $warnings = substr_count($logs, 'warning');

        $this->info("Errors: $errors, Warnings: $warnings");

        if ($errors > 10) {
            $content = "Errors: $errors, Warnings: $warnings";
            $email = new Email();
            $email->sendEmail('mehdi.rapoo77@gmail.com', $content, 'Alerte : Trop d\'erreurs dans les logs - Heberginfos');
        }
    }
}