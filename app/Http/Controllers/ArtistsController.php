<?php

namespace App\Http\Controllers;

use App\Services\Discogs;
use App\Models\UserArtist;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        $releases = $this->discogs->all(function ($page) use ($artist) {
            return $this->discogs->search([
                'type' => 'release',
                'query' => $artist->name,
                'page' => $page,
                'format' => 'vinyl,Limited Edition',
                'per_page' => 100,
            ]);
        });

        return view('artists.index', [
            'artist' => $artist,
            'releases' => $this->matchToArtist($artist, $releases),
        ]);
    }

    private function matchToArtist($artist, Collection $results)
    {
        return $results->filter(function ($release) use ($artist) {
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
    }
}
