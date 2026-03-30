<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\PanelRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('昵称')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('panel_role_label')
                    ->label('角色')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        '超级管理员' => 'danger',
                        '管理员' => 'warning',
                        default => 'info',
                    }),
                IconColumn::make('email_verified_at')
                    ->label('邮箱已验证')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('panel_role')
                    ->label('角色')
                    ->options(PanelRole::options()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
