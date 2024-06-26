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

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Besoin',
            self::PLANNED => 'Planifié',
            self::IN_PROGRESS => 'En cours',
            self::EVALUATION => 'En évaluation',
            self::EVALUATED => 'Évalué',
            self::INVOICED => 'Facturé',
        };
    }

    public static function getList(): array
    {
        return [
            self::DRAFT->value => 'Besoin',
            self::PLANNED->value => 'Planifié',
            self::IN_PROGRESS->value => 'En cours',
            self::EVALUATION->value => 'En évaluation',
            self::EVALUATED->value => 'Évalué',
            self::INVOICED->value => 'Facturé',
        ];
    }
}
