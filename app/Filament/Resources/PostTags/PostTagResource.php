<?php

namespace App\Filament\Resources\PostTags;

use App\Filament\Resources\PostTags\Pages\CreatePostTag;
use App\Filament\Resources\PostTags\Pages\EditPostTag;
use App\Filament\Resources\PostTags\Pages\ListPostTags;
use App\Filament\Resources\PostTags\Schemas\PostTagForm;
use App\Filament\Resources\PostTags\Tables\PostTagsTable;
use App\Models\PostTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PostTagResource extends Resource
{
    protected static ?string $model = PostTag::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = '标签';

    protected static string | \UnitEnum | null $navigationGroup = '内容';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = '标签';

    protected static ?string $pluralModelLabel = '标签';

    public static function form(Schema $schema): Schema
    {
        return PostTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostTagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug'];
    }

    public static function getRecordTitle(?Model $record): string
    {
        return $record?->name ?? '标签';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostTags::route('/'),
            'create' => CreatePostTag::route('/create'),
            'edit' => EditPostTag::route('/{record}/edit'),
        ];
    }
}
