<?php

namespace App\Filament\Resources\Resources\Schemas;

use App\Models\PostCategory;
use App\Models\PostTag;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 3,
                ])
                    ->schema([
                        Section::make('基础内容')
                            ->schema([
                                TextInput::make('title')
                                    ->label('标题')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('subtitle')
                                    ->label('副标题')
                                    ->maxLength(255)
                                    ->helperText('显示在前台资源详情页标题下方，可留空。'),
                                FileUpload::make('cover_path')
                                    ->label('缩略图')
                                    ->image()
                                    ->disk('public')
                                    ->directory('resources/covers')
                                    ->visibility('public')
                                    ->helperText('可单独上传封面；如果留空，系统会自动使用截图列表里的第一张作为缩略图。'),
                                RichEditor::make('description')
                                    ->label('正文详情')
                                    ->required()
                                    ->maxLength(65535),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 2,
                            ]),
                        Grid::make(1)
                            ->schema([
                                Section::make('分类与标签')
                                    ->schema([
                                        Select::make('category')
                                            ->label('分类')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->options(fn (): array => PostCategory::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'name')
                                                ->all())
                                            ->helperText('分类来自“内容 > 分类”。'),
                                        Select::make('tags')
                                            ->label('标签')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->options(fn (): array => PostTag::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'name')
                                                ->all())
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('标签名称')
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->createOptionUsing(function (array $data): string {
                                                $name = trim((string) ($data['name'] ?? ''));
                                                $slug = Str::slug($name);

                                                $tag = PostTag::query()->firstOrCreate(
                                                    ['slug' => $slug !== '' ? $slug : Str::lower(Str::random(8))],
                                                    [
                                                        'name' => $name,
                                                        'description' => null,
                                                    ],
                                                );

                                                if ($tag->name !== $name) {
                                                    $tag->forceFill(['name' => $name])->save();
                                                }

                                                return $tag->name;
                                            })
                                            ->helperText('可直接选择已有标签，也可在这里临时新建单个标签。'),
                                        Textarea::make('new_tags')
                                            ->label('批量新增标签')
                                            ->rows(4)
                                            ->placeholder("例如：\n剧情向\n汉化资源\n校园")
                                            ->helperText('支持一行一个，或使用中文/英文逗号分隔。保存资源时会自动创建并加入标签。'),
                                    ]),
                                Section::make('截图')
                                    ->description('正常上传截图即可，顺序会同步到前台详情页。')
                                    ->schema([
                                        FileUpload::make('screenshots')
                                            ->label('截图')
                                            ->image()
                                            ->multiple()
                                            ->reorderable()
                                            ->disk('public')
                                            ->directory('resources/screenshots')
                                            ->visibility('public')
                                            ->helperText('可拖动调整顺序；如果封面未上传，第一张截图会自动作为缩略图。')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
