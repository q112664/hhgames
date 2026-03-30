<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class AppearanceSettings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationLabel = '外观';

    protected static string | \UnitEnum | null $navigationGroup = '设置';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = '外观设置';

    protected ?string $subheading = '管理前台导航栏菜单，桌面端和移动端会共同使用这套配置。';

    protected string $view = 'filament.pages.appearance-settings';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'navbar_menu_items' => SiteSetting::current()->navbar_menu_items
                ?? SiteSetting::defaultNavbarMenuItems(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('导航栏菜单')
                        ->description('可调整菜单名称、链接和分组，前台会按当前顺序显示。')
                        ->schema([
                            Repeater::make('navbar_menu_items')
                                ->label('菜单项')
                                ->default(SiteSetting::defaultNavbarMenuItems())
                                ->reorderable()
                                ->addActionLabel('添加菜单项')
                                ->schema([
                                    TextInput::make('label')
                                        ->label('菜单名称')
                                        ->required()
                                        ->maxLength(40),
                                    TextInput::make('href')
                                        ->label('菜单链接')
                                        ->required()
                                        ->maxLength(2048)
                                        ->helperText('支持站内相对路径，例如 /resources?sort=latest'),
                                    Select::make('group')
                                        ->label('菜单分组')
                                        ->options([
                                            '站点入口' => '站点入口',
                                            '资源浏览' => '资源浏览',
                                        ])
                                        ->default('站点入口')
                                        ->required(),
                                ])
                                ->columns(3)
                                ->minItems(1),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('保存外观设置')
                                ->submit('save'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $current = SiteSetting::current();

        SiteSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'site_name' => $current->site_name,
                'site_url' => $current->site_url,
                'logo_path' => $current->logo_path,
                'navbar_menu_items' => $data['navbar_menu_items'] ?? SiteSetting::defaultNavbarMenuItems(),
            ],
        );

        Notification::make()
            ->success()
            ->title('外观设置已保存')
            ->send();
    }
}
