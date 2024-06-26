<?php

namespace App\Filament\Resources\FormationResource\RelationManagers;

use App\Mail\FormateurAffected;
use App\Models\Intervention;
use Filament\Forms\Components\Repeater;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

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
                            ->native(false)
                            ->minutesStep(15)
                            ->required(),

                        TimePicker::make('morning_end_time')
                            ->label('Fin de matinée')
                            ->seconds(false)
                            ->default('12:30')
                            ->native(false)
                            ->minutesStep(15)
                            ->required(),

                        TimePicker::make('afternoon_start_time')
                            ->label("Début d'après-midi")
                            ->seconds(false)
                            ->nullable()
                            ->native(false)
                            ->minutesStep(15)
                            ->default('13:30'),

                        TimePicker::make('afternoon_end_time')
                            ->label("Fin d'après-midi")
                            ->seconds(false)
                            ->native(false)
                            ->minutesStep(15)
                            ->default('17:00'),
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
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn ($record) => ! auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->disabled(fn () => ! auth()->user()->can('delete', Intervention::class)),
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

        $formateur = User::with('interventions')->where('id', $data['user_id'])->first();

        foreach ($dates as $date) {
            foreach ($formateur->interventions as $intervention) {
                if ($intervention->date === $date['date']) {
                    Notification::make()
                        ->title('Le formateur ' . $formateur->name . ' est déjà affecté à une formation le ' . $date['date'])
                        ->warning()
                        ->send();
                }
            }
        }

        // Notification d'affectation
        Mail::to(User::find($data['user_id'])->email)->send(new FormateurAffected($this->ownerRecord, $dates));

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
