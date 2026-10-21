<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Concerns\HandlesOptimisticLocking;
use App\Filament\Resources\TenantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    use HandlesOptimisticLocking;

    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
