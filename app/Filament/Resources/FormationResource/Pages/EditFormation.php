<?php

namespace App\Filament\Resources\FormationResource\Pages;

use App\Filament\Resources\FormationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormation extends EditRecord
{
    protected static string $resource = FormationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
