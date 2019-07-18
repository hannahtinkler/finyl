<?php

namespace App\Services;

use Log;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class Discogs
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param SpotifyWebAPI $api
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function search(array $args = [])
    {
        return $this->request('database/search', $args);
    }

    private function request(string $endpoint, array $args = [])
    {
        $key = serialize([
            'endpoint' => $endpoint,
            'args' => $args,
        ]);

        if (!$result = cache()->get($key)) {
            $result = json_decode(
                $this->client->get($this->buildUrl($endpoint, $args))->getBody()
                ,
                true
            );

            cache()->put($key, $result, 3600);
        }

        return $result;
    }

    private function buildUrl(string $endpoint, array $args = [])
    {
        return sprintf(
            'https://api.discogs.com/%s?token=%s&%s',
            $endpoint,
            config('services.discogs.api_token'),
            http_build_query($args)
        );
    }
}
