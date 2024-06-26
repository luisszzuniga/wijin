<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.edit-profile';

    public string $name = '';
    public string $email = '';
    public string $password = '';

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nom')
                ->default(auth()->user()->name)
                ->required(),

            TextInput::make('email'),

            TextInput::make('password')
                ->hint('Laissez vide pour ne pas changer le mot de passe')
                ->autocomplete('new-password')
                ->password(),
        ]);
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'password' => 'nullable|min:8',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        auth()->user()->update($data);

        $this->password = '';

        Notification::make()
            ->title('Profil mis à jour')
            ->success()
            ->send();
    }
}
