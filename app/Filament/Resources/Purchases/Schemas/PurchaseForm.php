<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseForm
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
                TextInput::make('amount_cents')
                    ->label('Valor (centavos)')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('paid_at')
                    ->label('Pago em')
                    ->required(),
                DateTimePicker::make('expires_at')
                    ->label('Expira em (vazio = vitalício)'),
                TextInput::make('gateway')
                    ->label('Gateway')
                    ->required()
                    ->default('fake'),
                TextInput::make('external_payment_id')
                    ->label('ID pagamento externo'),
            ]);
    }
}
