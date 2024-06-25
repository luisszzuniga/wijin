<?php

namespace App\Console\Commands;

use App\Enums\FormationStatusEnum;
use App\Models\Formation;
use App\Models\Intervention;
use Illuminate\Console\Command;

class UpdateFirstSessionFormationsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-first-session-formations-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interventions = Intervention::select('interventions.*')
            ->join('formations', 'interventions.formation_id', '=', 'formations.id')
            ->where('date', date("Y-m-d"))
            ->where('formations.status', FormationStatusEnum::PLANNED)
            ->get();

        foreach ($interventions as $inter) {
            $firstInter = Intervention::where('formation_id', $inter->formation_id)
                ->orderBy('date')
                ->first();

            if ($firstInter->id == $inter->id) {
                Formation::where('id', $inter->formation_id)
                    ->update(['status' => FormationStatusEnum::IN_PROGRESS]);
            }
        }
    }
}
