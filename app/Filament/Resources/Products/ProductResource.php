<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'товар';

    protected static ?string $pluralModelLabel = 'Товары';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Название')->required()->maxLength(255),
            Select::make('category_id')->label('Раздел или подраздел')->relationship('category', 'name')->searchable()->preload()->placeholder('Без раздела'),
            TextInput::make('slug')->label('Артикул')->required()->alphaDash()->maxLength(255)->unique(ignoreRecord: true),
            Textarea::make('description')->label('Описание')->required()->maxLength(10000),
            FileUpload::make('image')->label('Главная картинка')->disk('public')->directory('products')->visibility('public')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)->preventFilePathTampering()->helperText('JPG, PNG или WebP до 5 МБ. Рекомендуется 1200 × 800.'),
            FileUpload::make('gallery')->label('Дополнительные картинки')->disk('public')->directory('products/gallery')->visibility('public')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->multiple()->reorderable()->maxFiles(8)->maxSize(5120)->preventFilePathTampering()->helperText('До 8 изображений, порядок меняется перетаскиванием.'),
            TextInput::make('sort_order')->label('Порядок в каталоге')->integer()->minValue(0)->maxValue(100000)->default(0)->required(),
            TextInput::make('price')->label('Цена в копейках (10000 = 100 ₽)')->integer()->minValue(1)->maxValue(100000000)->required(),
            TextInput::make('download_url')->label('Ссылка на программу')->url()->required()->maxLength(2048)->rules(['regex:~^https?://~i']),
            Toggle::make('is_active')->label('Опубликован'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')->label('Картинка')->disk('public'),
            TextColumn::make('name')->label('Товар')->searchable(),
            TextColumn::make('category.name')->label('Раздел')->placeholder('Без раздела')->searchable(),
            TextColumn::make('price')->label('Цена')->money('RUB', divideBy: 100)->sortable(),
            TextColumn::make('available_keys_count')->counts('availableKeys')->label('Свободно ключей'),
            IconColumn::make('is_active')->label('Опубликован')->boolean(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProducts::route('/')];
    }
}
