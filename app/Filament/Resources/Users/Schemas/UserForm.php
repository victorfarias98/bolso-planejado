<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label('E-mail verificado em'),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Toggle::make('is_admin')
                    ->label('Administrador')
                    ->default(false),
                Select::make('plan_id')
                    ->label('Plano (snapshot)')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('premium_expires_at')
                    ->label('Premium expira em'),
            ]);
    }
}
