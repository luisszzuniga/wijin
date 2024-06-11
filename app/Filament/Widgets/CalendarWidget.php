<?php

namespace App\Filament\Widgets;

use App\Enums\UserRoleEnum;
use App\Models\Intervention;
use Carbon\Carbon;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Support\Str;

class CalendarWidget extends FullCalendarWidget
{
    public ?int $selectedUserId = null;

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'timeGridDay,timeGridWeek,dayGridMonth',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $start = Carbon::parse($fetchInfo['start'])->toDate();
        $end = Carbon::parse($fetchInfo['end'])->toDate();

        $interventions = Intervention::where('date', '>=', $start)
            ->where('date', '<=', $end)
            ->with([
                'formation.promotion.school',
                'formation.module',
            ]);
        
        if ($this->selectedUserId && auth()->user()->role === UserRoleEnum::Admin) {
            $interventions->where('user_id', $this->selectedUserId);
        }

        if (auth()->user()->role === UserRoleEnum::Formateur) {
            $interventions->where('user_id', auth()->id());
        }

        $interventions = $interventions->get();

        $events = [];
        foreach ($interventions as $intervention) {
            foreach (json_decode($intervention->time) as $time) {
                $start = Carbon::parse($intervention->date)->setTimeFromTimeString($time->start)->toISOString();
                $end = Carbon::parse($intervention->date)->setTimeFromTimeString($time->end)->toISOString();

                $title = $intervention->formation->promotion->school->name . ' - ' .        
                    $intervention->formation->promotion->name . ' - ' .
                    $intervention->formation->module->name;

                $events[] = EventData::make()
                    ->id(Str::uuid())
                    ->title($title)
                    ->start($start)
                    ->end($end)
                    ->toArray();
            }
        }

        return $events;
    }
}
