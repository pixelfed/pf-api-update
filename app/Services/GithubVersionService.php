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
        $api = 'https://api.github.com/repos/pixelfed/pixelfed/releases';
        $res = Http::acceptJson()->get($api);

        if(!$res->ok()) {
            return false;
        }

        $json = $res->json();

        return $json;
    }
}
