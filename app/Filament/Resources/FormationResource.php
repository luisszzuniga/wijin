<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormationResource\Pages;
use App\Filament\Resources\FormationResource\RelationManagers;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Promotion;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
                    ->required()
                    ->options(Module::pluck('name', 'id'))
                    ->searchable(),

                Select::make('promotion_id')
                    ->required()
                    ->options(Promotion::pluck('name', 'id'))
                    ->searchable(),

                Textarea::make('aleas')
                    ->nullable(),

                TextInput::make('hourly_price_ht')
                    ->required()
                    ->type('number')
                    ->step('0.01'),

                SpatieMediaLibraryFileUpload::make('syllabus')
                    ->collection('syllabus')
                    ->multiple(),

                SpatieMediaLibraryFileUpload::make('evaluation')
                    ->collection('evaluation')
                    ->multiple(),
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
