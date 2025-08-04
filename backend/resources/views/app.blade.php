<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Load file build của React (CRA) --}}
    <link rel="stylesheet" href="{{ asset('app/static/css/main.5f87265d.css') }}">
  </head>
  <body>
    <div id="root"></div>

    <script src="{{ asset('app/static/js/453.8166b796.chunk.js') }}"></script>
    <script src="{{ asset('app/static/js/main.19b82fc5.js') }}"></script>
  </body>
</html>