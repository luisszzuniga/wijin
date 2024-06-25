<?php

namespace App\Filament\Pages;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Filament\Pages\Page;

class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static string $view = 'filament.pages.calendar';

    public array $users;
    public ?int $selectedUserId = null;

    public function mount()
    {
        $this->users = User::pluck('name', 'id')
            ->toArray();
    }
}
