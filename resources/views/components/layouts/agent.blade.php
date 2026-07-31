<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="CapBay Vroom sales agent portal.">
        <title>{{ $title ?? 'Sales agent portal' }} - {{ config('app.name') }}</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('css/vroom.css') }}">
        @fonts
        @livewireStyles
    </head>
    <body>
        <header class="agent-header">
            <div class="container-fluid agent-header__inner">
                <a href="{{ route('agent.registrations.index') }}" class="brand brand--light">
                    <span class="brand__mark brand__mark--light">V</span>
                    <span>Sales agent portal</span>
                </a>

                <div class="agent-header__account">
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('agent.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-agent-logout">Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="agent-page">
            {{ $slot }}
        </main>

        @livewireScripts
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
