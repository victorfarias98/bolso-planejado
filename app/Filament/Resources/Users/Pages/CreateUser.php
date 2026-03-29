<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => 'Informe uma senha com pelo menos 8 caracteres.',
            ]);
        }

        if (strlen((string) $data['password']) < 8) {
            throw ValidationException::withMessages([
                'password' => 'A senha deve ter pelo menos 8 caracteres.',
            ]);
        }

        $data['password'] = Hash::make($data['password']);

        return $data;
    }
}
