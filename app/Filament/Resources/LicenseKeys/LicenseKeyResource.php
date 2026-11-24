<?php

namespace App\Filament\Resources\LicenseKeys;

use App\Filament\Resources\LicenseKeys\Pages\ManageLicenseKeys;
use App\Models\LicenseKey;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LicenseKeyResource extends Resource
{
    protected static ?string $model = LicenseKey::class;

    protected static ?string $modelLabel = 'ключ';

    protected static ?string $pluralModelLabel = 'Ключи активации';

    public static function canEdit(Model $record): bool
    {
        return $record->order_id === null;
    }

    public static function canDelete(Model $record): bool
    {
        return $record->order_id === null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_id')->label('Товар')->relationship('product', 'name')->searchable()->preload()->required(),
            TextInput::make('key')->label('Ключ активации')->password()->revealable()->required()->maxLength(4096)
                ->rules([fn (?LicenseKey $record) => function (string $attribute, $value, $fail) use ($record) {
                    if (LicenseKey::where('fingerprint', hash('sha256', trim($value)))->when($record, fn ($q) => $q->whereKeyNot($record->id))->exists()) {
                        $fail('Этот ключ уже добавлен.');
                    }
                }]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('product.name')->label('Товар')->searchable(),
            TextColumn::make('order_id')->label('Выдан заказу')->placeholder('Свободен'),
            TextColumn::make('created_at')->label('Добавлен')->dateTime('d.m.Y H:i'),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageLicenseKeys::route('/')];
    }
}
