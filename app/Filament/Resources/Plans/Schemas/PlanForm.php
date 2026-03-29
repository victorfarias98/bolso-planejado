<?php

namespace App\Filament\Resources\Plans\Schemas;

use App\Enums\PlanBillingMode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('name')
                    ->label('Nome')
                    ->required(),
                Select::make('billing_mode')
                    ->label('Modo de cobrança')
                    ->options([
                        PlanBillingMode::Free->value => 'Gratuito',
                        PlanBillingMode::Subscription->value => 'Assinatura',
                        PlanBillingMode::OneTime->value => 'Pagamento único',
                    ])
                    ->required(),
                TextInput::make('price_cents')
                    ->label('Preço (centavos)')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('currency')
                    ->label('Moeda')
                    ->required()
                    ->default('BRL'),
                TextInput::make('interval')
                    ->label('Intervalo (month / year)')
                    ->placeholder('month'),
                Toggle::make('active')
                    ->label('Ativo')
                    ->required(),
            ]);
    }
}
