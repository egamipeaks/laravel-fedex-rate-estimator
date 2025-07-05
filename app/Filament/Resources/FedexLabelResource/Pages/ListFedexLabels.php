<?php

namespace App\Filament\Resources\FedexLabelResource\Pages;

use App\Filament\Resources\FedexLabelResource;
use App\Models\FedexLabel;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFedexLabels extends ListRecords
{
    protected static string $resource = FedexLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                )->badge(FedexLabel::query()->count()),
            'active' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->notDelivered()
                )->badge(FedexLabel::notDelivered()->count()),
            'delivered' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->delivered()
                )->badge(FedexLabel::delivered()->count()),
            'cancelled' => Tab::make()
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->cancelled()
                )->badge(FedexLabel::cancelled()->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
