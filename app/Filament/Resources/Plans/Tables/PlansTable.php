<?php

namespace App\Filament\Resources\Plans\Tables;

use App\Enums\PlanBillingMode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('billing_mode')
                    ->label('Modo')
                    ->badge()
                    ->formatStateUsing(function (PlanBillingMode|string|null $state): string {
                        $v = $state instanceof PlanBillingMode ? $state->value : (string) $state;

                        return match ($v) {
                            'free' => 'Gratuito',
                            'subscription' => 'Assinatura',
                            'one_time' => 'Pagamento único',
                            default => $v,
                        };
                    })
                    ->searchable(),
                TextColumn::make('price_cents')
                    ->label('Preço (centavos)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Moeda')
                    ->searchable(),
                TextColumn::make('interval')
                    ->label('Intervalo')
                    ->searchable(),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
