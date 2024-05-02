<?php

namespace App\Observers;

use App\Models\Intervention;

class InterventionObserver
{
    public function creating(Intervention $intervention)
    {
        foreach (json_decode($intervention->time) as $time) {
            $intervention->duration += strtotime($time->end) - strtotime($time->start);
        }
    }
}
