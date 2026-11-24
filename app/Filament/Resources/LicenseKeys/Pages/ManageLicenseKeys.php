<?php

namespace App\Filament\Resources\LicenseKeys\Pages;

use App\Filament\Resources\LicenseKeys\LicenseKeyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLicenseKeys extends ManageRecords
{
    protected static string $resource = LicenseKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
