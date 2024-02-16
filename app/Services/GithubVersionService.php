<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GithubVersionService
{
    public static function urgent()
    {
        $res = config('api.urgent.versions');
        if(!$res || empty($res) || strlen($res) < 5) {
            return [];
        }
        return explode(',', $res);
    }

    public static function get()
    {
        return Cache::remember('gvs:latest-versions:v1.2', 900, function() {
            $api = 'https://api.github.com/repos/pixelfed/pixelfed/releases?per_page=6';
            $res = Http::withToken(config('api.gh_token'))->acceptJson()->get($api);

            if(!$res->ok()) {
                return false;
            }

            $json = $res->json();

            return $json;
        });
    }
}
