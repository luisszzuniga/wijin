<?php
use App\Enums\UserRoleEnum;
?>

<x-filament-panels::page>
    @if (auth()->user()->role === UserRoleEnum::Admin)
    <x-filament::input.wrapper>
        <x-filament::input.select wire:model.live="selectedUserId">
            <option value="">Tous les formateurs</option>
            @foreach ($users as $id => $user)
            <option value="{{ $id }}">{{ $user }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
    @endif

    @livewire(\App\Filament\Widgets\CalendarWidget::class,
        ['selectedUserId' => $selectedUserId],
        key(str()->random())
    )
</x-filament-panels::page>