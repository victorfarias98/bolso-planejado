<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchasesTable
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
                TextColumn::make('amount_cents')
                    ->label('Valor (centavos)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expira em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->searchable(),
                TextColumn::make('external_payment_id')
                    ->label('ID pagamento externo')
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
