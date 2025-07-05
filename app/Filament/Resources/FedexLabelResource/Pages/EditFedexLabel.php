<?php

namespace App\Filament\Resources\FedexLabelResource\Pages;

use App\Filament\Resources\FedexLabelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFedexLabel extends EditRecord
{
    protected static string $resource = FedexLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
