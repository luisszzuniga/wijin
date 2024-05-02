<?php

namespace App\Filament\Resources\FormationResource\Pages;

use App\Filament\Resources\FormationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormations extends ListRecords
{
    protected static string $resource = FormationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
