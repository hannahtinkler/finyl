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
        $artist = $this->artist->whereSpotifyId($spotifyId)->firstOrFail();

        $releases = $this->discogs->search([
            'type' => 'release',
            'query' => $artist->name,
            'format' => 'vinyl,Limited Edition',
        ]);

        return view('artists.index', [
            'artist' => $artist,
            'releases' => $releases['results'],
        ]);
    }
}
