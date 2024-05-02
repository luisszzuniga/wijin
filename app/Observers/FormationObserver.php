<?php

namespace App\Observers;

use App\Enums\FormationStatusEnum;
use App\Models\Formation;

class FormationObserver
{
    public function creating(Formation $formation)
    {
        $formation->status = FormationStatusEnum::DRAFT->value;
    }
}
