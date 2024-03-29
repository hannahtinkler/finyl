<?php

namespace App\Services;

use Log;
use App\Models\User;
use Illuminate\Http\Request;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use SpotifyWebAPI\Session as SpotifySession;

class Spotify
{
    /**
     * @var SpotifyWebAPI
     */
    private $api;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var boolean
     */
    private $hasSetToken = false;

    /**
     * @param SpotifyWebAPI $api
     */
    public function __construct(SpotifySession $session, SpotifyWebAPI $api)
    {
        $this->api = $api;
        $this->session = $session;
    }

    public function __call($method, $args)
    {
        try {
            $this->setToken();

            return $this->api->$method(...$args);
        } catch (SpotifyWebAPIException $exception) {
            if ($exception->getCode() === 401) {
                $this->refreshToken();
                return $this->api->$method(...$args);
            }

            throw $exception;
        }
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user ?: auth()->user();
    }

    /**
     * @return SpotifyAuthentication
     */
    public function refreshToken()
    {
        $this->session->refreshAccessToken(
            $this->getUser()->spotify_refresh_token
        );

        $this->getUser()->update([
            'spotify_access_token' => $this->session->getAccessToken(),
        ]);

        $this->setUser = $this->getUser()->refresh();
    }

    /**
     * @return SpotifyAuthentication
     */
    private function setToken()
    {
        $this->hasSetToken = $this->api->setAccessToken(
            $this->getUser()->spotify_access_token
        );
    }
}
