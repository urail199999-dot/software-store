<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Models\Order;
use App\Services\FulfillOrder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')->columns([
            TextColumn::make('id')->label('Заказ')->sortable(),
            TextColumn::make('product_name')->label('Товар'),
            TextColumn::make('email')->label('Покупатель')->searchable(),
            TextColumn::make('amount')->label('Сумма')->money('RUB', divideBy: 100),
            TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn ($state) => $state === 'paid' ? 'Оплачен' : 'Ожидает оплаты')->color(fn ($state) => $state === 'paid' ? 'success' : 'warning'),
            TextColumn::make('paid_at')->label('Оплачен')->dateTime('d.m.Y H:i'),
        ])->filters([SelectFilter::make('status')->label('Статус')->options(['pending' => 'Ожидает оплаты', 'paid' => 'Оплачен'])])
            ->recordActions([
                Action::make('confirmPayment')->label('Подтвердить оплату')->requiresConfirmation()
                    ->modalDescription('Подтверждайте только после получения денег. Покупателю откроется ключ и ссылка на программу.')
                    ->visible(fn (Order $record) => $record->status === 'pending')
                    ->action(function (Order $record) {
                        app(FulfillOrder::class)->handle($record, auth()->user());
                        Notification::make()->title('Оплата подтверждена, ключ выдан')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageOrders::route('/')];
    }
}
