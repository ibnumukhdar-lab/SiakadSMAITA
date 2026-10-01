@php
    $pengaturan = \App\Models\Pengaturan::first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pengaturan->nama_sekolah ?? config('app.name', 'SIAKAD') }}</title>

        @if($pengaturan && $pengaturan->logo_path)
            <link rel="icon" type="image/png" href="{{ asset('berkas/' . $pengaturan->logo_path) }}">
        @else
            <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        
        {{ $slot }}

    </body>
</html>