<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MeController extends Controller
{
    public function index(Request $request)
    {
        return view('me.index', [
            'artists' => \App\Models\UserArtist::all(),//$request->user()->artists,
        ]);
    }
}
