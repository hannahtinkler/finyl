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
        return $this->get('database/search', $args);
    }

    /**
     * Get the default response for the endpoint (e.g. if pagincated, the first
     * page)
     *
     * @param  string $endpoint
     * @param  array  $args
     * @return array
     */
    private function get(string $endpoint, array $args = [])
    {
        $key = serialize([
            'endpoint' => $endpoint,
            'args' => $args,
        ]);

        $result = cache()->remember($key, 3600, function () use ($endpoint, $args) {
            return json_decode(
                $this->client->get($this->buildUrl($endpoint, $args))->getBody(),
                true
            );
        });

        return collect($result);
    }

    /**
     * Gets all records from an endpoint (e.g. if paginated, all pages)
     *
     * @return Collection
     */
    public function all(Callable $request)
    {
        $page = 1;
        $allResults = collect([]);
        $maxPages = 999;

        while ($page <= $maxPages && $results = $request($page)) {
            $maxPages = $results['pagination']['pages'];
            $allResults = $allResults->merge($results['results'] ?? $results);
            $page++;
        }

        return $allResults;
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
