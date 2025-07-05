<?php

namespace App\Filament\Resources;

use App\Enums\FedexImportMode;
use App\Enums\FedexLabelStatus;
use App\Filament\Resources\FedexLabelResource\Pages;
use App\Jobs\RefreshFedexLabelsJob;
use App\Models\FedexLabel;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class FedexLabelResource extends Resource
{
    protected static ?string $model = FedexLabel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'tracking_number';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Tabs')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Info')
                            ->schema([
                                Infolists\Components\TextEntry::make('tracking_number'),
                                Infolists\Components\TextEntry::make('status'),
                                Infolists\Components\TextEntry::make('reference_number'),
                                Infolists\Components\TextEntry::make('purchase_order'),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Estimate')
                            ->schema([
                                Infolists\Components\KeyValueEntry::make('estimate_data')
                                    ->label('Estimate Data')
                                    ->columns(2)
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->columnSpanFull(),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Website Data')
                            ->schema([
                                Infolists\Components\KeyValueEntry::make('raw_metadata')
                                    ->label('Website Table Data')
                                    ->columns(2)
                                    ->keyLabel('Key')
                                    ->valueLabel('Value')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_number_and_po')
                    ->label('Reference / PO')
                    ->getStateUsing(
                        fn ($record): string => ($record->reference_number ? $record->reference_number : 'N/A')
                            .' / '
                            .($record->purchase_order ? $record->purchase_order : 'N/A')
                    )
                    ->searchable(['reference_number', 'purchase_order']),
                Tables\Columns\TextColumn::make('recipient')
                    ->label('Recipient')
                    ->getStateUsing(
                        fn (FedexLabel $record): string => ($record->recipient_name ?? 'N/A')
                            .' - '
                            .($record->recipient_city)
                    ),
                Tables\Columns\TextColumn::make('has_estimate')
                    ->label('Estimate')
                    ->getStateUsing(
                        fn (FedexLabel $record): string => ($record->estimates()->exists() ? 'Yes' : 'No')
                    ),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fedex_link')
                    ->getStateUsing(
                        fn (FedexLabel $record): string => sprintf(
                            '<div class="%s"><a class="underline" href="%s" target="_blank">%s</a></div>',
                            'text-sm leading-6',
                            $record->tracking_link,
                            'FedEx Link'
                        )
                    )->html(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(FedexLabelStatus::class)
                    ->multiple(),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refreshLabels')
                    ->label('Refresh Labels')
                    ->requiresConfirmation()
                    ->action(function () {
                        $lockKey = RefreshFedexLabelsJob::CACHE_KEY;

                        if (Cache::has($lockKey)) {
                            Notification::make()
                                ->title('Import already in progress')
                                ->body('Please wait until the current FedEx import finishes.')
                                ->warning()
                                ->send();

                            return;
                        }

                        RefreshFedexLabelsJob::dispatch(
                            FedexImportMode::MANUAL
                        );

                        Notification::make()
                            ->title('Import started')
                            ->body('Fetching labels in the background. You’ll be notified when it’s done.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFedexLabels::route('/'),
            'view' => Pages\ViewFedexLabel::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()->notDelivered()->count();
    }
}
