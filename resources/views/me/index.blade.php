@extends('layouts.app')


@section('content')

    <ul>
        @foreach($artists as $artist)
            <li>
                <a href="{{ route('artists.show', $artist->spotify_id) }}">
                    {{ $artist->name }}
                </a>
            </li>
        @endforeach
    </ul>

@endsection
