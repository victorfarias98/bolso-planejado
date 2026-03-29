<?php

namespace App\Filament\Resources\Features\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Chave (interna)')
                    ->required(),
                TextInput::make('label')
                    ->label('Rótulo')
                    ->required(),
            ]);
    }
}
