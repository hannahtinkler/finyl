<?php

namespace App\Http\Controllers;

use Artisan;
use App\Models\User;
use App\Services\Spotify;
use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use SpotifyWebAPI\Session as SpotifySession;

class AuthenticationController extends Controller
{
    /**
     * @var SpotifySession
     */
    protected $session;

    /**
     * @var $options
     */
    protected $options = [
        'scope' => [
            'user-follow-read',
        ],
    ];

    public function __construct(
        SpotifySession $session,
        SpotifyWebAPI $api,
        Spotify $spotify
    ) {
        $this->session = $session;
        $this->api = $api;
        $this->spotify = $spotify;
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect($this->session->getAuthorizeUrl($this->options));
    }

    /**
     * @todo refresh token
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->session->requestAccessToken($request->get('code'));
        } catch (SpotifyWebAPIException $exception) {
            if ($exception->getCode() === 401) {
                $this->spotify->refreshToken();
                return $this->store($request);
            }

            throw $exception;
        }

        $accessToken = $this->session->getAccessToken();

        $this->api->setAccessToken($accessToken);

        $me = $this->api->me();

        $user = User::firstOrCreate([
            'spotify_id' => $me->id,
        ], [
            'name' => $me->display_name,
            'spotify_access_token' => $accessToken,
            'spotify_refresh_token' => $this->session->getRefreshToken(),
        ]);

        auth()->login($user);

        Artisan::call('spotify:import-followed');

        return redirect()->route('me');
    }

    public function destroy()
    {
        auth()->logout();

        return redirect()->route('login');
    }
}
