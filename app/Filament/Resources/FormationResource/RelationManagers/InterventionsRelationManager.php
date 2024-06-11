<?php

namespace App\Filament\Resources\FormationResource\RelationManagers;

use Filament\Forms\Get;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InterventionsRelationManager extends RelationManager
{
    protected static string $relationship = 'interventions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('is_recurrent')
                    ->label('Récurrent')
                    ->reactive()
                    ->default(false)
                    ->hidden(fn (string $operation) => $operation === 'edit'),

                Select::make('user_id')
                    ->label('Formateur')
                    ->searchable()
                    ->required()
                    ->options(fn () => User::pluck('name', 'id')),

                Select::make('frequency')
                    ->label('Fréquence')
                    ->options([
                        'weekly' => 'Toutes les semaines',
                        'biweekly' => 'Toutes les deux semaines',
                        'triweekly' => 'Toutes les trois semaines',
                        'monthly' => 'Tous les mois',
                    ])
                    ->hidden(fn (Get $get) => $get('is_recurrent') === false)
                    ->requiredIf('is_recurrent', true),

                Select::make('days')
                    ->label('Jours')
                    ->multiple()
                    ->options([
                        'monday' => 'Lundi',
                        'tuesday' => 'Mardi',
                        'wednesday' => 'Mercredi',
                        'thursday' => 'Jeudi',
                        'friday' => 'Vendredi',
                        'saturday' => 'Samedi',
                        'sunday' => 'Dimanche',
                    ])
                    ->hidden(fn (Get $get) => $get('is_recurrent') === false)
                    ->requiredIf('is_recurrent', true),

                DatePicker::make('date')
                    ->label(fn (Get $get) => $get('is_recurrent') ? 'Date de début' : 'Date')
                    ->native(false)
                    ->minDate(now())
                    ->closeOnDateSelection()
                    ->columnSpan(fn (Get $get, string $operation) => $get('is_recurrent') || $operation === "edit" ? 1 : 'full')
                    ->required(),

                DatePicker::make('end_date')
                    ->label('Date de fin')
                    ->native(false)
                    ->minDate(now())
                    ->closeOnDateSelection()
                    ->hidden(fn (Get $get) => $get('is_recurrent') === false)
                    ->requiredIf('is_recurrent', true),

                Fieldset::make('Horaires')
                    ->schema([
                        TimePicker::make('morning_start_time')
                            ->label('Début de matinée')
                            ->displayFormat('H:i')
                            ->default('09:00')
                            ->required(),

                        TimePicker::make('morning_end_time')
                            ->label('Fin de matinée')
                            ->displayFormat('H:i')
                            ->default('12:30')
                            ->required(),

                        TimePicker::make('afternoon_start_time')
                            ->label("Début d'après-midi")
                            ->displayFormat('H:i')
                            ->default('13:30')
                            ->required(),

                        TimePicker::make('afternoon_end_time')
                            ->label("Fin d'après-midi")
                            ->displayFormat('H:i')
                            ->default('17:00')
                            ->required(),
                    ]),

                Textarea::make('comment')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date'),

                TextColumn::make('duration')
                    ->formatStateUsing(fn ($record) => gmdate('H\hi', $record->duration)),

                TextColumn::make('formateur.name')
                    ->searchable()
                    ->label('Formateur'),

                TextColumn::make('comment')
                    ->words(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $intervention = $this->setTime($data);
                        $this->createInterventions($intervention);

                        return $intervention;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data) => $this->reverseTime($data))
                    ->mutateFormDataUsing(fn (array $data) => $this->setTime($data)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Set time for intervention
     *
     * @param array $data
     * @return array
     */
    private function setTime(array $data): array {
        $data['time'] = json_encode([
            ["start" => $data['morning_start_time'], "end" => $data['morning_end_time']],
            ["start" => $data['afternoon_start_time'], "end" => $data['afternoon_end_time']],
        ]);

        unset($data['morning_start_time']);
        unset($data['morning_end_time']);
        unset($data['afternoon_start_time']);
        unset($data['afternoon_end_time']);

        return $data;
    }

    /**
     * Reverse time for intervention
     *
     * @param array $data
     * @return array
     */
    private function reverseTime(array $data): array {
        $time = json_decode($data['time']);

        $data['morning_start_time'] = $time[0]->start;
        $data['morning_end_time'] = $time[0]->end;
        $data['afternoon_start_time'] = $time[1]->start;
        $data['afternoon_end_time'] = $time[1]->end;

        unset($data['time']);

        return $data;
    }

    /**
     * Create interventions from recurrence
     *
     * @param array $data
     * @return void
     */
    private function createInterventions(array $data): void {
        // Créer toutes les interventions à partir de la recurrence
        if (!$data['is_recurrent']) {
            return;
        }

        $startDate = $data['date'];
        $endDate = $data['end_date'];
        $days = $data['days'];
        $frequency = $data['frequency'];

        $interventions = [];

        while ($startDate <= $endDate) {
            if (in_array(strtolower($startDate->format('l')), $days)) {
                $interventions[] = [
                    'user_id' => $data['user_id'],
                    'date' => $startDate,
                    'time' => $data['time'],
                    'comment' => $data['comment'],
                ];
            }

            $startDate = match ($frequency) {
                'weekly' => $startDate->addWeek(),
                'biweekly' => $startDate->addWeeks(2),
                'triweekly' => $startDate->addWeeks(3),
                'monthly' => $startDate->addMonth(),
            };
        }

        $this->createMany($interventions);
    }

    private function createMany(array $interventions): void {
        foreach ($interventions as $intervention) {
            $this->getModel()::create($intervention);
        }
    }
}
