<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Spotify;
use Illuminate\Console\Command;

class ImportFollowedArtists extends Command
{
    /**
     * @var array|null
     */
    private $lastArtist;

    /**
     * @var string
     */
    protected $signature = 'spotify:import-followed';

    /**
     * @var string
     */
    protected $description = 'Imports followed artists from Spotify';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Spotify $spotify, User $user)
    {
        parent::__construct();

        $this->user = $user;
        $this->spotify = $spotify;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = $this->user->whereNotNull('spotify_access_token');

        $this->progress = $this->output->createProgressBar($users->count());
        $this->progress->start();

        $users->each([$this, 'importArtistsForUser']);

        $this->progress->finish();
    }

    public function importArtistsForUser(User $user)
    {
        $user->artists()->delete();

        while ($artists = $this->retrieveArtists($user)) {
            $artists = collect($artists)
                ->map(function ($artist) use ($user) {
                    return [
                        'user_id' => $user->id,
                        'name' => $artist->name,
                        'spotify_id' => $artist->id,
                    ];
                })
                ->toArray();

            $user->artists()->createMany($artists);

            $this->lastArtist = array_pop($artists);
        }

        $this->progress->advance();
    }

    private function retrieveArtists(User $user)
    {
        return $this->spotify
            ->setUser($user)
            ->getUserFollowedArtists([
                'after' => $this->lastArtist['spotify_id'] ?? null,
            ])
            ->artists
            ->items;
    }
}
