<?php

namespace App\Filament\Resources;

use App\Enums\FormationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\FormationResource\Pages;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Promotion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\FormationResource\RelationManagers\InterventionsRelationManager;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;

class FormationResource extends Resource
{
    protected static ?string $model = Formation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('module_id')
                    ->required()
                    ->options(Module::pluck('name', 'id'))
                    ->label('Module')
                    ->searchable()
                    ->disabled(auth()->user()->role !== UserRoleEnum::Admin),

                Select::make('promotion_id')
                    ->required()
                    ->options(function () {
                        $promotions = Promotion::with('school')->get();
                        $options = [];
                        foreach ($promotions as $promotion) {
                            $options[$promotion->id] = $promotion->name . ' (' . $promotion->school->name . ')';
                        }
                        return $options;
                    })
                    ->label('Promotion')
                    ->searchable()
                    ->disabled(auth()->user()->role !== UserRoleEnum::Admin),

                Textarea::make('aleas')
                    ->nullable(),

                TextInput::make('hourly_price_ht')
                    ->required()
                    ->label('Prix horaire HT')
                    ->type('number')
                    ->step('0.01')
                    ->disabled(auth()->user()->role !== UserRoleEnum::Admin),

                SpatieMediaLibraryFileUpload::make('syllabus')
                    ->collection('syllabus')
                    ->multiple(),

                SpatieMediaLibraryFileUpload::make('evaluation')
                    ->collection('evaluation')
                    ->multiple(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Fieldset::make('Facturation')
                    ->schema([
                        TextEntry::make('facturationInfos') 
                            ->label('Infos de facturation')
                            ->copyable()
                            ->columnSpanFull()
                            ->copyMessage('Copié!')
                            ->hintAction(
                                Action::make('Facturer')
                                    ->icon('heroicon-m-document-check')
                                    ->disabled(fn ($record) => $record->status !== FormationStatusEnum::EVALUATED->value)
                                    ->action(function ($record) {
                                        $record->status = FormationStatusEnum::INVOICED->value;
                                        $record->save();

                                        Notification::make()
                                            ->title('Formation facturée !')
                                            ->success()
                                            ->send();
                                    })
                            ),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('promotion.name')
                    ->searchable()
                    ->label('Promotion'),

                TextColumn::make('promotion.school.name')
                    ->searchable()
                    ->badge()
                    ->label('Ecole'),

                TextColumn::make('module.name')
                    ->searchable()
                    ->label('Module'),
                TextColumn::make('status')
                    ->searchable()
                    ->label('Statut')
                    ->formatStateUsing(function ($state) {
                        return FormationStatusEnum::tryFrom($state)?->getLabel() ?? $state;
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(FormationStatusEnum::getList())
                    ->label('Statut'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(fn ($record) => ! auth()->user()->can('create', Formation::class)),

                Tables\Actions\ViewAction::make()
                    ->label('Facturation')
                    ->disabled(fn ($record) => $record->status !== FormationStatusEnum::EVALUATED->value && $record->status !== FormationStatusEnum::INVOICED->value),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->disabled(fn () => ! auth()->user()->can('delete')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InterventionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormations::route('/'),
            'create' => Pages\CreateFormation::route('/create'),
            'edit' => Pages\EditFormation::route('/{record}/edit'),
        ];
    }
}
