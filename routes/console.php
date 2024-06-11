<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use \App\Models\Intervention;
use \App\Models\Formation;
use \App\Enums\FormationStatusEnum;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * Modifie le statut de la formation en cours si c'est la première intervention
 */
Schedule::call(function () {
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
})->timezone('Europe/Paris')->dailyAt('16:17');

/**
 * Modifie le statut de la formation en évaluation si c'est la dernière intervention
 */
Schedule::call(function () {
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
})->timezone('Europe/Paris')->dailyAt('18:00');
