<?php

namespace App\Console\Commands;

use App\Models\Intervention;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MensualFormationsNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mensual-formations-notification';

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
        Carbon::setLocale('fr');

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
            $interventionsText = [];

            foreach ($interventions as $inter) {
                $date = Carbon::parse($inter->date)->isoFormat('dddd D MMMM');
                $date = ucwords($date);
                
                $times = [];
                foreach (json_decode($inter->time) as $time) {
                    $times[] = $time->start . ' à ' . $time->end;
                }

                $times = implode(' et ', $times);

                $interventionsText[] = 'Le ' . $date . ' de ' . $times . ' chez ' . $inter->school_name . ' au ' . $inter->address . '.' . "\n";
            }

            Mail::to($user->email)->send(new \App\Mail\MensualFormationsNotification($interventionsText));
        }
    }
}
