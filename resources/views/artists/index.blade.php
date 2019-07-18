@extends('layouts.app')


@section('content')

   {{ $artist->name }}

    <ul>
        @foreach ($releases as $release)
            <li>
                <a href="https://www.discogs.com/sell/release/{{ $release['id'] }}" target="_blank">
                    @if($release['thumb'])
                        <img src="{{ $release['thumb'] }}" alt="{{ $release['title'] }}" />
                    @endif

                    {{ $release['title'] }}
                </a>
            </li>
        @endforeach
    </ul>

@endsection
