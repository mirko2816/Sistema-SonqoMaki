<?php

namespace App\Modules\RoutineTemplates\Actions;

use App\Models\RoutineTemplate;
use Illuminate\Support\Facades\DB;

class ArchiveRoutineTemplate
{
    public function handle(int $templateId): bool
    {
        return DB::transaction(function () use ($templateId): bool {
            $template = RoutineTemplate::withTrashed()->lockForUpdate()->findOrFail($templateId);
            if ($template->trashed()) {
                return false;
            }

            $template->status = RoutineTemplate::STATUS_ARCHIVED;
            $template->save();
            $template->delete();

            return true;
        });
    }
}
