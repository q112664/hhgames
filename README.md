# hhgames

基于 Laravel 13、Inertia.js、React 19、TypeScript 和 Tailwind CSS v4 的游戏资源站示例项目。

项目包含：

- 前台资源列表与详情页
- Filament 后台管理
- 资源分类、标签、点赞、浏览量、下载区块
- 示例资源 seed
- `Singureo` / `Shionlib` 示例资源导入

## 技术栈

- PHP `8.3+`
- Laravel `13`
- Inertia.js `2`
- React `19`
- TypeScript
- Tailwind CSS `v4`
- Filament `v4`

## 本地环境

推荐环境：

- Laravel Herd
- PHP `8.3+`
- Node.js `20+`
- Composer `2+`

## 快速开始

### 1. 安装依赖

```bash
composer install
npm install
```

### 2. 配置环境

```bash
copy .env.example .env
php artisan key:generate
```

如果你使用 SQLite，可直接确认数据库文件存在：

```bash
type nul > database\database.sqlite
```

然后检查 `.env` 中至少包含以下配置：

```env
APP_NAME=hhgames
APP_URL=http://games.test

DB_CONNECTION=sqlite
DB_DATABASE=D:\laravel\games\database\database.sqlite
```

如果你使用 Laravel Herd，通常只需要把站点目录加入 Herd，并让本地域名指向：

- `http://games.test`

如果你开启了 HTTPS，也可以直接访问：

- `https://games.test`

### 3. 执行迁移和 seed

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### 4. 启动开发环境

```bash
composer run dev
```

或者分别启动：

```bash
php artisan serve
npm run dev
```

## 一键初始化

项目已经提供了 `setup` 脚本：

```bash
composer run setup
```

它会执行：

- `composer install`
- 复制 `.env`
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install`
- `npm run build`

## 访问地址

前台资源页：

- `/`
- `/resources`

后台管理入口：

- `/admin`

例如 Herd 本地地址：

- [首页](http://games.test/)
- [资源列表](http://games.test/resources)
- [后台登录](http://games.test/admin)

## 默认管理员账号

执行 `php artisan db:seed` 后，`DatabaseSeeder` 会创建一个后台管理员：

- 邮箱：`test@example.com`
- 密码：`password`

后台路径来自 [AdminPanelProvider.php](D:/laravel/games/app/Providers/Filament/AdminPanelProvider.php)，当前是：

- `/admin`

## 常用命令

开发：

```bash
composer run dev
```

前端构建：

```bash
npm run build
```

代码格式检查：

```bash
vendor\bin\pint
npm run lint
npm run types:check
```

测试：

```bash
php artisan test
```

只跑 Singureo 抓取解析测试：

```bash
php artisan test --filter=SingureoScraper
```

## 示例资源 Seeder

当前项目内置两类示例资源：

- `ShionlibPlaceholderResourceSeeder`
- `SingureoPlaceholderResourceSeeder`

它们已经接入 [DatabaseSeeder.php](D:/laravel/games/database/seeders/DatabaseSeeder.php)，因此执行：

```bash
php artisan db:seed
```

会自动一起导入。

如果你只想单独导入 `Singureo` 示例资源：

```bash
php artisan db:seed --class=SingureoPlaceholderResourceSeeder
```

如果你想重新抓取并更新本地快照：

```bash
php artisan resources:cache-singureo --limit=20
```

详细说明见：

- [Singureo Seeder 文档](D:/laravel/games/docs/singureo-placeholder-seeder.md)

## 目录说明

关键目录如下：

- `app/Http/Controllers`
- `app/Models`
- `app/Services`
- `app/Filament`
- `database/migrations`
- `database/seeders`
- `resources/js`
- `resources/views`
- `routes`
- `tests`

## 同步到其他站点时的注意事项

仓库默认不会提交以下本地运行产物：

- `.env`
- `vendor`
- `node_modules`
- `database/database.sqlite`
- `storage/app/public` 下的抓取图片和调试截图
- Laravel 运行缓存和日志

因此你拉取仓库后，需要自行执行：

```bash
composer install
npm install
php artisan migrate
php artisan db:seed
php artisan storage:link
```

## 说明

这个项目当前已经做了多轮前台和后台功能调整，包括：

- 资源卡片移动端两列
- 详情页点赞逻辑优化
- 浏览量统计接入
- 详情头部长标题适配
- 分页滚动体验优化
- `Singureo` 示例资源抓取与本地快照 seed

如果后续还要继续整理仓库，建议下一步补：

- 部署文档
- 数据库结构说明
- 管理后台使用说明
- 示例截图
