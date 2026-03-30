<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
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
class SiteSettings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = '站点设置';

    protected static string | \UnitEnum | null $navigationGroup = '设置';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = '站点设置';

    protected ?string $subheading = '这里维护前台实际使用的站点标题、站点链接和导航栏 LOGO。';

    protected string $view = 'filament.pages.site-settings';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('基础信息')
                        ->description('这些内容会直接同步到前台标题、导航栏品牌区和页脚。')
                        ->schema([
                            TextInput::make('site_name')
                                ->label('站点标题')
                                ->helperText('用于浏览器标题后缀、导航栏标题和页脚名称。')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('site_url')
                                ->label('站点链接')
                                ->helperText('用于站点对外链接展示。')
                                ->required()
                                ->url()
                                ->maxLength(2048),
                            FileUpload::make('logo_path')
                                ->label('LOGO')
                                ->helperText('上传后会显示在前台导航栏和页脚，建议使用方形图片。')
                                ->image()
                                ->disk('public')
                                ->directory('site-settings')
                                ->visibility('public'),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('保存设置')
                                ->submit('save'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::query()->updateOrCreate(
            ['id' => 1],
            $data,
        );

        Notification::make()
            ->success()
            ->title('站点设置已保存')
            ->send();
    }
}
