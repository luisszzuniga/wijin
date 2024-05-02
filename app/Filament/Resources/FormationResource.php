<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormationResource\Pages;
use App\Filament\Resources\FormationResource\RelationManagers;
use App\Models\Formation;
use App\Models\Module;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FormationResource extends Resource
{
    protected static ?string $model = Formation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('module_id')
                    ->options(Module::pluck('name', 'id'))
                    ->searchable(),

                Select::make('school_id')
                    ->options(School::pluck('name', 'id'))
                    ->searchable(),

                Textarea::make('aleas')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
