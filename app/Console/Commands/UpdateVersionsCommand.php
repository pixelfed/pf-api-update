<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\GithubVersionService;

class UpdateVersionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-versions-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update versions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $res = collect(GithubVersionService::get())
        ->map(function($version) {
            $name = $version['name'];

            return [
                'id' => $version['id'],
                'name' => $version['name'],
                'version' => substr($version['name'], 1),
                'url' => $version['html_url'],
                'published_at' => $version['published_at']
            ];
        });
        $latest = $res->shift();
        $response = [
            'latest' => $latest,
            'older' => $res,
        ];

        file_put_contents(public_path('versions.json'), json_encode($response, JSON_UNESCAPED_SLASHES));

        $this->info('Finished generating version data!');
        $this->line(' ');
        $this->info(url('versions.json'));
    }
}
