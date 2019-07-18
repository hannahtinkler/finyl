<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="canonical" href="{{ request()->url() }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset(mix('css/app.css')) }}"/>

    <title>{{ config('app.name') }}</title>
  </head>

  <body>
    <div id="app">
      @yield('content')
    </div>

    <script type="text/javascript" src="{{ asset(mix('js/app.js')) }}"></script>
  </body>
</html>


