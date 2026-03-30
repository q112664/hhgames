<?php

namespace App\Filament\Resources\Resources;

use App\Filament\Resources\Resources\Pages\CreateResource;
use App\Filament\Resources\Resources\Pages\EditResource;
use App\Filament\Resources\Resources\Pages\ListResources;
use App\Filament\Resources\Resources\Schemas\ResourceForm;
use App\Filament\Resources\Resources\Tables\ResourcesTable;
use App\Models\Resource as ResourceModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = '资源';

    protected static string | \UnitEnum | null $navigationGroup = '内容';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = '资源';

    protected static ?string $pluralModelLabel = '资源';

    public static function form(Schema $schema): Schema
    {
        return ResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResourcesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'subtitle', 'slug', 'category'];
    }

    public static function getRecordTitle(?Model $record): string
    {
        return $record?->title ?? '资源';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResources::route('/'),
            'create' => CreateResource::route('/create'),
            'edit' => EditResource::route('/{record}/edit'),
        ];
    }
}
