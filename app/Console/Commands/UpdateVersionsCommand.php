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
        $api = 'https://api.github.com/repos/pixelfed/pixelfed/releases';
        $res = Http::acceptJson()->get($api);

        if(!$res->ok()) {
            return;
        }

        $versions = $res->json();

        if(!$versions) {
            return;
        }

        $urgent = GithubVersionService::urgent();

        $versionsMap = collect($versions)->map(function($ver, $idx) use($urgent) {
            return [
                'version' => str_starts_with($ver['tag_name'], 'v') ? substr($ver['tag_name'], 1) : $ver['tag_name'],
                'latest' => $idx == 0,
                'urgent' => in_array($ver['tag_name'], $urgent),
                'published_at' => $ver['published_at'],
                'release_url' => $ver['html_url'],
                'release_notes' => $ver['body'],
            ];
        })->take(4);

        $updates = [
            "updatesAvailable" => $versionsMap
        ];

        Storage::put('public/api/latest.json', json_encode($updates, JSON_UNESCAPED_SLASHES));

        $this->info('Finished generating version data!');
        $this->line(' ');
        $this->info(url('public/api/latest.json'));
    }
}
