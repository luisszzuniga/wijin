<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Carbon;
use \App\Models\Intervention;
use \App\Models\Formation;
use \App\Enums\FormationStatusEnum;
use \App\Models\User;

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
})->timezone('Europe/Paris')->dailyAt('7:00');

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

Schedule::call(function () {
    $users = User::all();
    $nextMonth = Carbon::now()->addMonth();

    foreach ($users as $user) {
        // Récupère les interventions sur le mois prochain
        $interventions = Intervention::select('interventions.*', 'modules.name', 'schools.name as school_name', 'schools.address')
            ->join('formations', 'interventions.formation_id', '=', 'formations.id')
            ->join('modules', 'formations.module_id', '=', 'modules.id')
            ->join('promotions', 'formations.promotion_id', '=', 'promotions.id')
            ->join('schools', 'promotions.school_id', '=', 'schools.id')
            ->where('user_id', $user->id)
            ->where('date', '>=', $nextMonth->startOfMonth()->format('Y-m-d'))
            ->where('date', '<=', $nextMonth->endOfMonth()->format('Y-m-d'))
            ->get();

        if ($interventions->count() === 0) {
            continue;
        }

        /**
         * @example
         * Le lundi 1er janvier de 8h à 12h chez Nom de l'école au 1 rue de l'école.
         */
        $interString = '';

        foreach ($interventions as $inter) {
            // TODO :
            //  Gérer les dates en français
            //  Ajouter les heures
            $date = date('l t F', strtotime($inter->date));

            $interString .= 'Le ' . $date . ' de ' . ' chez ' . $inter->school_name . ' au ' . $inter->address . '.' . "\n";
        }

        $this->comment($interString);
    }
})->timezone('Europe/Paris')->monthlyOn(1, '7:00');
