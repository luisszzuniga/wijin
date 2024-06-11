<?php

namespace App\Observers;

use App\Enums\FormationStatusEnum;
use App\Models\Formation;
use App\Models\Intervention;

class InterventionObserver
{
    public function creating(Intervention $intervention): void
    {
        // Calcule la durée de l'intervention en secondes
        foreach (json_decode($intervention->time) as $time) {
            $intervention->duration += strtotime($time->end) - strtotime($time->start);
        }

        Formation::where('id', $intervention->formation_id)
            ->update(['status' => FormationStatusEnum::PLANNED]);
    }
}
