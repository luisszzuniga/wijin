<?php

namespace App\Enums;

enum FormationStatusEnum: string
{
    case DRAFT = 'draft'; // Besoin
    case PLANNED = 'planned'; // Planifié
    case IN_PROGRESS = 'in_progress'; // En cours
    case EVALUATION = 'evaluation'; // En évaluation
    case EVALUATED = 'evaluated'; // Évalué
    case INVOICED = 'invoiced'; // Facturé
}
