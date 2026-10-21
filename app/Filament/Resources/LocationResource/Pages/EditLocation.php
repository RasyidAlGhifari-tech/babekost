<?php

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Concerns\HandlesOptimisticLocking;
use App\Filament\Resources\LocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocation extends EditRecord
{
    use HandlesOptimisticLocking;

    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
