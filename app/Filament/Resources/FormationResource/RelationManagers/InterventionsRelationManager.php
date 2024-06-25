<?php

namespace App\Filament\Resources\FormationResource\RelationManagers;

use App\Models\Intervention;
use Filament\Forms\Components\Repeater;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;

class InterventionsRelationManager extends RelationManager
{
    protected static string $relationship = 'interventions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Formateur')
                    ->searchable()
                    ->required()
                    ->columnSpan('full')
                    ->options(fn () => User::pluck('name', 'id')),

                Repeater::make('dates')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Date')
                            ->native(false)
                            ->minDate(now())
                            ->closeOnDateSelection()
                            ->required()
                            ->disabledOn('edit'),
                    ])
                    ->addActionLabel('Ajouter une date')
                    ->columnSpanFull()
                    ->grid(2)
                    ->hiddenOn('edit'),

                DatePicker::make('date')
                    ->label('Date')
                    ->native(false)
                    ->minDate(now())
                    ->closeOnDateSelection()
                    ->hiddenOn('create')
                    ->required(),

                Fieldset::make('Horaires')
                    ->schema([
                        TimePicker::make('morning_start_time')
                            ->label('Début de matinée')
                            ->seconds(false)
                            ->default('09:00')
                            ->required(),

                        TimePicker::make('morning_end_time')
                            ->label('Fin de matinée')
                            ->seconds(false)
                            ->default('12:30')
                            ->required(),

                        TimePicker::make('afternoon_start_time')
                            ->label("Début d'après-midi")
                            ->seconds(false)
                            ->default('13:30')
                            ->required(),

                        TimePicker::make('afternoon_end_time')
                            ->label("Fin d'après-midi")
                            ->seconds(false)
                            ->default('17:00')
                            ->required(),
                    ]),

                Textarea::make('comment')
                    ->label("Commentaire")
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
                    ->formatStateUsing(fn ($record) => gmdate('H\hi', $record->duration))
                    ->label('Durée'),

                TextColumn::make('formateur.name')
                    ->searchable()
                    ->label('Formateur'),

                TextColumn::make('comment')
                    ->words(50)
                    ->label('Commentaire'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->disabled(fn () => ! auth()->user()->can('create', Intervention::class))
                    ->mutateFormDataUsing(function (array $data) {
                        $intervention = $this->setTime($data);
                        return $this->createInterventions($intervention);
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
    private function createInterventions(array $data): array {
        // Créer toutes les interventions à partir des dates
        $dates = $data['dates'];

        foreach ($dates as $key => $date) {
            // Si c'est la dernière date, on la return
            if ($key === count($dates) - 1) {
                return [
                    'user_id' => $data['user_id'],
                    'formation_id' => $this->ownerRecord->id,
                    'date' => $date['date'],
                    'time' => $data['time'],
                    'comment' => $data['comment'],
                ];
            }

            Intervention::create([
                'user_id' => $data['user_id'],
                'formation_id' => $this->ownerRecord->id,
                'date' => $date['date'],
                'time' => $data['time'],
                'comment' => $data['comment'],
            ]);
        }
    }
}
