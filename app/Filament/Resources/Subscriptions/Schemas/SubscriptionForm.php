<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Enums\SubscriptionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        SubscriptionStatus::Active->value => 'Ativa',
                        SubscriptionStatus::Canceled->value => 'Cancelada',
                        SubscriptionStatus::PastDue->value => 'Em atraso',
                        SubscriptionStatus::Trialing->value => 'Período de teste',
                    ])
                    ->required(),
                DateTimePicker::make('current_period_start')
                    ->label('Início do período'),
                DateTimePicker::make('current_period_end')
                    ->label('Fim do período'),
                TextInput::make('gateway')
                    ->label('Gateway')
                    ->required()
                    ->default('fake'),
                TextInput::make('external_id')
                    ->label('ID externo (provedor)'),
            ]);
    }
}
