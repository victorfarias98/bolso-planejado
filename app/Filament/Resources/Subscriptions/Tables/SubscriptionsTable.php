<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Enums\SubscriptionStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsTable
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
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable(),
                TextColumn::make('plan.name')
                    ->label('Plano')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function (SubscriptionStatus|string|null $state): string {
                        $v = $state instanceof SubscriptionStatus ? $state->value : (string) $state;

                        return match ($v) {
                            'active' => 'Ativa',
                            'canceled' => 'Cancelada',
                            'past_due' => 'Em atraso',
                            'trialing' => 'Período de teste',
                            default => $v,
                        };
                    })
                    ->searchable(),
                TextColumn::make('current_period_start')
                    ->label('Início do período')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('current_period_end')
                    ->label('Fim do período')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->searchable(),
                TextColumn::make('external_id')
                    ->label('ID externo')
                    ->searchable(),
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
