<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use Filament\Actions\DeleteAction;
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
use Illuminate\Database\Eloquent\Model;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $modelLabel = 'раздел';

    protected static ?string $pluralModelLabel = 'Разделы и подразделы';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Название')->required()->maxLength(255),
            TextInput::make('slug')->label('Адрес раздела')->helperText('Латиница, цифры и дефисы, например: games или utilities.')->required()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)->unique(ignoreRecord: true),
            Select::make('parent_id')->label('Родительский раздел')->placeholder('Нет — это основной раздел')->searchable()
                ->options(fn (?Category $record) => Category::query()->when($record, fn ($query) => $query->whereNotIn('id', $record->descendantIds()))->orderBy('name')->pluck('name', 'id'))
                ->rules(['nullable', 'exists:categories,id', fn (?Category $record) => function (string $attribute, $value, $fail) use ($record) {
                    if ($record && in_array((int) $value, $record->descendantIds(), true)) {
                        $fail('Выберите другой родительский раздел.');
                    }
                }]),
            Textarea::make('description')->label('Описание')->maxLength(10000),
            FileUpload::make('image')->label('Обложка раздела')->disk('public')->directory('categories')->visibility('public')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)->preventFilePathTampering()->helperText('JPG, PNG или WebP, до 5 МБ. Рекомендуется 1200 × 800.'),
            TextInput::make('sort_order')->label('Порядок')->integer()->minValue(0)->maxValue(100000)->default(0)->required(),
            Toggle::make('is_active')->label('Опубликован')->helperText('Скрытый раздел скрывает также подразделы и их товары.'),
        ]);
    }

    public static function canDelete(Model $record): bool
    {
        return ! $record->children()->exists() && ! $record->products()->exists();
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            ImageColumn::make('image')->label('Обложка')->disk('public'),
            TextColumn::make('name')->label('Название')->searchable(),
            TextColumn::make('parent.name')->label('Внутри раздела')->placeholder('Основной раздел'),
            TextColumn::make('products_count')->counts('products')->label('Товаров'),
            TextColumn::make('sort_order')->label('Порядок')->sortable(),
            IconColumn::make('is_active')->label('Опубликован')->boolean(),
        ])->recordActions([EditAction::make(), DeleteAction::make()->tooltip('Удалить можно пустой раздел. Сначала перенесите его товары и подразделы.')]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCategories::route('/')];
    }
}
