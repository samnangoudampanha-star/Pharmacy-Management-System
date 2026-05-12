<!doctype html>
<html lang="{{ app()->getLocale() }}" data-locale="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Hanuman:wght@400;700&family=Roboto:wght@400;500;700&display=swap"
    rel="stylesheet">

  <title inertia>{{ config('app.name', 'Pharmacy MS') }}</title>

  @routes
  @vite(['resources/css/app.scss', 'resources/js/app.js'])
  @inertiaHead

  @stack('styles')
</head>
