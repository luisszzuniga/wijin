<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'admin' => Tab::make('Admins')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', UserRoleEnum::Admin->value)),
            'formateur' => Tab::make('Formateurs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', UserRoleEnum::Formateur->value)),
        ];
    }
}
