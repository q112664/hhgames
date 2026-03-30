<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\PanelRole;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('账户信息')
                    ->schema([
                        TextInput::make('name')
                            ->label('昵称')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('邮箱')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('panel_role')
                            ->label('后台角色')
                            ->options(PanelRole::options())
                            ->required()
                            ->native(false)
                            ->default(PanelRole::Editor->value),
                    ])
                    ->columns(3),
                Section::make('资料与安全')
                    ->schema([
                        FileUpload::make('avatar_path')
                            ->label('头像')
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public'),
                        TextInput::make('password')
                            ->label('密码')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->saved(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
