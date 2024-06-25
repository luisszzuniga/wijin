<?php

use App\Console\Commands\MensualFormationsNotification;
use App\Console\Commands\UpdateFirstSessionFormationsStatus;
use App\Console\Commands\UpdateLastSessionFormationsStatus;
use Illuminate\Support\Facades\Schedule;

/**
 * Modifie le statut de la formation en cours si c'est la première intervention
 */
Schedule::command(UpdateFirstSessionFormationsStatus::class)->timezone('Europe/Paris')->dailyAt('7:00');

/**
 * Modifie le statut de la formation en évaluation si c'est la dernière intervention
 */
Schedule::command(UpdateLastSessionFormationsStatus::class)->timezone('Europe/Paris')->dailyAt('18:00');

/**
 * Envoie une notification aux utilisateurs pour les formations du mois prochain
 */
// Schedule::command(MensualFormationsNotification::class)->timezone('Europe/Paris')->monthlyOn(1, '7:00');
Schedule::command(MensualFormationsNotification::class)->everyMinute();
