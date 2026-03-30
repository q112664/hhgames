# Singureo 示例资源 Seeder 使用文档

本文档说明如何使用当前项目里的 `Singureo` 示例资源导入方案，并说明如何迁移到其他 Laravel 站点做测试。

## 文件结构

当前功能由以下文件组成：

- `app/Services/SingureoScraper.php`
- `database/seeders/SingureoPlaceholderResourceSeeder.php`
- `database/seeders/data/singureo-placeholder-resources.php`
- `routes/console.php`
- `app/Services/ResourceThumbnailService.php`

各文件职责如下：

- `SingureoScraper.php`
  - 用于从 `https://www.singureo.com/` 抓取文章列表和详情页信息。
  - 可抓标题、分类、标签、封面、简介段落、截图等公开内容。

- `SingureoPlaceholderResourceSeeder.php`
  - 将抓取结果或本地快照写入 `resources` 表。
  - 下载封面和截图到本地 `storage/app/public`。
  - 生成适合前台展示的示例统计数据、文件信息和评论预览。

- `database/seeders/data/singureo-placeholder-resources.php`
  - 本地数据快照。
  - Seeder 会优先读取这个文件，因此即使没有外网也能直接导入。

- `routes/console.php`
  - 提供命令 `php artisan resources:cache-singureo --limit=20`。
  - 用于重新从源站抓取并生成本地快照。

- `ResourceThumbnailService.php`
  - 为封面和截图生成缩略图，供前台卡片和详情页使用。

## 当前项目中的使用方式

### 1. 直接导入本地快照

如果你只想把这 20 条测试资源导入当前站点，直接执行：

```bash
php artisan db:seed --class=SingureoPlaceholderResourceSeeder
```

说明：

- 该命令会优先读取本地快照文件：
  - `database/seeders/data/singureo-placeholder-resources.php`
- 当前快照中已经保存了 `20` 条资源数据。
- 执行后会写入 `resources` 表，并下载封面、截图到本地公开存储目录。

### 2. 重新抓取最新示例并更新快照

如果你想重新从 `Singureo` 生成一份新的测试数据快照，执行：

```bash
php artisan resources:cache-singureo --limit=20
```

说明：

- 该命令会联网抓取 `Singureo` 的最新文章。
- 抓取结果会保存到：
  - `database/seeders/data/singureo-placeholder-resources.php`
- 之后再次执行 seeder 时，会优先使用这份本地快照。

### 3. 全量执行 DatabaseSeeder

当前项目的 `DatabaseSeeder` 已经接入了这个 seeder：

- `database/seeders/DatabaseSeeder.php`

所以也可以直接执行：

```bash
php artisan db:seed
```

注意：

- 当前 `DatabaseSeeder` 不只会跑 `Singureo`，也会执行项目里其他资源 seed。

## 迁移到其他 Laravel 站点的最小方案

如果你只是想把这套测试数据能力复制到另一个 Laravel 项目，最少需要带走以下文件：

- `database/seeders/SingureoPlaceholderResourceSeeder.php`
- `database/seeders/data/singureo-placeholder-resources.php`
- `app/Services/ResourceThumbnailService.php`

另外还需要确认目标项目已经具备以下条件：

- 存在 `resources` 表，并且字段结构与当前项目兼容，至少包含：
  - `slug`
  - `title`
  - `subtitle`
  - `category`
  - `content_rating`
  - `cover_path`
  - `summary`
  - `description`
  - `published_at`
  - `tags`
  - `platforms`
  - `basic_info`
  - `files`
  - `screenshots`
  - `comments_preview`
  - `views_count`
  - `downloads_count`
  - `favorites_count`
  - `comments_count`
  - `rating_value`

- 存在 `App\Models\Resource` 模型，并且 fillable/casts 支持上述字段。
- 已配置 `public` 存储磁盘。
- 已执行：

```bash
php artisan storage:link
```

如果目标项目也想支持“重新联网抓取并刷新快照”，还需要再带上：

- `app/Services/SingureoScraper.php`
- `routes/console.php` 中的 `resources:cache-singureo` 命令

## 推荐迁移步骤

### 方案 A：只要固定测试数据

适合测试站、演示站、模板站。

复制：

- `database/seeders/SingureoPlaceholderResourceSeeder.php`
- `database/seeders/data/singureo-placeholder-resources.php`
- `app/Services/ResourceThumbnailService.php`

然后在目标项目执行：

```bash
php artisan db:seed --class=SingureoPlaceholderResourceSeeder
```

优点：

- 不依赖外网
- 数据稳定
- 迁移最简单

### 方案 B：需要可刷新抓取

适合经常做示例库更新的项目。

复制：

- `app/Services/SingureoScraper.php`
- `database/seeders/SingureoPlaceholderResourceSeeder.php`
- `database/seeders/data/singureo-placeholder-resources.php`
- `app/Services/ResourceThumbnailService.php`
- `routes/console.php` 中对应命令

然后先执行：

```bash
php artisan resources:cache-singureo --limit=20
```

再执行：

```bash
php artisan db:seed --class=SingureoPlaceholderResourceSeeder
```

优点：

- 可以更新快照
- 可以调整导入数量

缺点：

- 首次抓取依赖外网
- 源站结构变化时，抓取逻辑可能需要同步调整

## 数据内容说明

这批示例资源的来源与处理方式如下：

- 标题、分类、标签、封面、截图、部分介绍段落来自公开页面
- 详情描述不是整篇原文照搬，而是整理后的摘要化内容
- 下载信息、热度、收藏、评论属于本地演示数据
- 资源 `slug` 统一为：

```text
singureo-{原文章ID}
```

例如：

- `singureo-0d3e3a62`
- `singureo-a5a625a3`

## 常用命令

生成本地快照：

```bash
php artisan resources:cache-singureo --limit=20
```

导入示例资源：

```bash
php artisan db:seed --class=SingureoPlaceholderResourceSeeder
```

执行全部 seed：

```bash
php artisan db:seed
```

运行解析测试：

```bash
php artisan test --filter=SingureoScraper
```

## 已知限制

- 当前快照默认保存 `20` 条资源。
- 如果源站 HTML 结构变化，`SingureoScraper.php` 可能需要调整解析规则。
- 如果目标项目的 `resources` 表字段不一致，seeder 需要按目标结构做映射。
- 如果目标项目没有安装图片处理依赖，缩略图生成可能失效，但原图仍可作为回退使用。

## 建议

如果你的目标是“多个站点重复做测试”，建议优先使用本地快照方案，不要每次都在线抓取。原因很简单：

- 数据稳定
- 导入速度更可控
- 不受外部站点波动影响
- 更适合测试 UI、筛选、分页、后台录入和详情展示
