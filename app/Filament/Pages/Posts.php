<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Posts extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = '游戏资源';

    protected static string | \UnitEnum | null $navigationGroup = '内容';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = '游戏资源';

    protected ?string $subheading = '这里维护的是游戏资源使用的分类和标签，后续资源内容会直接复用这套配置。';

    protected string $view = 'filament.pages.posts';
}
