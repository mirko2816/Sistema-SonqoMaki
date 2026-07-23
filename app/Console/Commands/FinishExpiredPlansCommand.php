<?php

namespace App\Console\Commands;

use App\Modules\Plans\Actions\FinishExpiredPlans;
use Illuminate\Console\Command;

class FinishExpiredPlansCommand extends Command
{
    protected $signature = 'plans:finish-expired';

    protected $description = 'Finaliza planes activos vencidos usando la fecha local de America/Lima';

    public function handle(FinishExpiredPlans $action): int
    {
        $count = $action->handle();
        $this->info("Planes finalizados: {$count}");

        return self::SUCCESS;
    }
}
