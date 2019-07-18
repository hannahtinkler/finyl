<?php

namespace App\Http\Controllers;

use App\Services\Discogs;
use App\Models\UserArtist;
use Illuminate\Http\Request;

class ArtistsController extends Controller
{
    /**
     * @var Discogs
     */
    protected $discogs;

    private $maxPages = 999;

    private $page = 1;

    /**
     * @param Discogs $discogs
     */
    public function __construct(Discogs $discogs, UserArtist $artist)
    {
        $this->discogs = $discogs;
        $this->artist = $artist;
    }

    public function show(Request $request, string $spotifyId)
    {
        $allReleases = [];
        $artist = $this->artist->whereSpotifyId($spotifyId)->firstOrFail();

        while ($this->page <= $this->maxPages && $releases = $this->getResults($artist)) {
            $this->maxPages = $releases['pagination']['pages'];

            $newReleases = collect($releases['results'])
                ->filter(function ($release) use ($artist) {
                    $artistName1 = preg_replace('/[^A-Za-z\s]+/', '', explode(' - ', $release['title'])[0]);
                    $artistName2 = preg_replace('/[^A-Za-z\s]+/', '', $artist->name);

                    return trim($artistName1) === trim($artistName2);
                })
                ->sort(function ($a, $b) {
                    return explode(' - ', $b['title'])[1] <=> explode(' - ', $a['title'])[1];
                })
                ->sort(function ($a, $b) {
                    return explode(' - ', $b['title'])[0] <=> explode(' - ', $a['title'])[0];
                });

            $allReleases = array_merge($allReleases, $newReleases->toArray());

            $this->page++;
        }

        return view('artists.index', [
            'artist' => $artist,
            'releases' => $allReleases,
        ]);
    }

    private function getResults(UserArtist $artist)
    {
        return $this->discogs->search([
            'type' => 'release',
            'query' => $artist->name,
            'page' => $this->page,
            'format' => 'vinyl,Limited Edition',
            'per_page' => 100,
        ]);
    }
}
