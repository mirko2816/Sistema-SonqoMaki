<?php

namespace App\Console\Commands;

use App\Modules\Reminders\Actions\ProcessDueReminders;
use Illuminate\Console\Command;

class ProcessDueRemindersCommand extends Command
{
    protected $signature = 'reminders:process-due';

    protected $description = 'Procesa los recordatorios del minuto actual en America/Lima sin reintentos';

    public function handle(ProcessDueReminders $action): int
    {
        $result = $action->handle();
        $this->info("Horarios: {$result['due']}; adquiridos: {$result['acquired']}; ya procesados: {$result['skipped']}");

        return self::SUCCESS;
    }
}
