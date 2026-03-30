<?php

namespace App\Filament\Resources\Resources\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_path')
                    ->label('缩略图')
                    ->disk('public')
                    ->circular(false)
                    ->square(),
                TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                TextColumn::make('subtitle')
                    ->label('副标题')
                    ->searchable()
                    ->limit(36)
                    ->wrap(),
                TextColumn::make('category')
                    ->label('分类')
                    ->badge()
                    ->sortable(),
                TextColumn::make('tags')
                    ->label('标签')
                    ->formatStateUsing(fn ($state): string => collect($state ?? [])
                        ->filter(fn ($tag): bool => is_string($tag) && $tag !== '')
                        ->take(3)
                        ->implode(' / '))
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
