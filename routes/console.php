<?php

use App\Services\SingureoScraper;
use Database\Seeders\SingureoPlaceholderResourceSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('resources:cache-singureo {--limit=20}', function (SingureoScraper $scraper) {
    $limit = max((int) $this->option('limit'), 1);
    $entries = $scraper->latestPosts($limit);
    $snapshotPath = base_path(SingureoPlaceholderResourceSeeder::SNAPSHOT_PATH);
    $directory = dirname($snapshotPath);

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $php = '<?php'.PHP_EOL.PHP_EOL.'return '.var_export($entries, true).';'.PHP_EOL;

    file_put_contents($snapshotPath, $php);

    $this->info(sprintf('Saved %d Singureo entries to %s', count($entries), $snapshotPath));
})->purpose('Cache Singureo resource data into a local snapshot file');
