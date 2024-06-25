<?php

namespace App\Console\Commands;

use App\Enums\FormationStatusEnum;
use App\Models\Formation;
use App\Models\Intervention;
use Illuminate\Console\Command;

class UpdateLastSessionFormationsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-formations-status';

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
            ->where('formations.status', FormationStatusEnum::IN_PROGRESS)
            ->get();

        foreach ($interventions as $inter) {
            $lastInter = Intervention::where('formation_id', $inter->formation_id)
                ->latest('date')
                ->first();

            if ($lastInter->id == $inter->id) {
                Formation::where('id', $inter->formation_id)
                    ->update(['status' => FormationStatusEnum::EVALUATION]);
            }
        }
    }
}
