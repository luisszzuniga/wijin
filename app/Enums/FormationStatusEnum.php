<?php

namespace App\Enums;

enum FormationStatusEnum: string
{
    case DRAFT = 'draft';
    case PLANNED = 'planned';
    case IN_PROGRESS = 'in_progress';
    case EVALUATION = 'evaluation';
    case EVALUATED = 'evaluated';
    case INVOICED = 'invoiced';
}