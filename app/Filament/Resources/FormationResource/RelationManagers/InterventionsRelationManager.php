<?php

namespace App\Filament\Resources\FormationResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InterventionsRelationManager extends RelationManager
{
    protected static string $relationship = 'interventions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->native(false)
                    ->required(),

                Select::make('user_id')
                    ->label('Formateur')
                    ->searchable()
                    ->required()
                    ->options(fn () => User::pluck('name', 'id')),

                TextInput::make('time')
                    ->default('[{"start": "09:00", "end": "12:00"}, {"start": "13:30", "end": "17:00"}]')
                    ->required(),

                Textarea::make('comment')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                Tables\Columns\TextColumn::make('date'),

                Tables\Columns\TextColumn::make('duration')
                    ->formatStateUsing(fn ($record) => gmdate('H\hi', $record->duration)),

                TextColumn::make('formateur.name')
                    ->searchable()
                    ->label('Formateur'),

                Tables\Columns\TextColumn::make('comment')
                    ->words(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
