<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Register your interest in a CapBay Vroom vehicle.">

        <title>{{ config('app.name', 'CapBay Vroom') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('css/vroom.css') }}">

        @fonts
    </head>
    <body>
        <main class="landing">
            <div class="landing__glow" aria-hidden="true"></div>

            <div class="container position-relative">
                <div class="row align-items-center g-5">
                <section class="hero col-lg-7">
                    <a href="{{ route('home') }}" class="brand" aria-label="CapBay Vroom home">
                        <span class="brand__mark">V</span>
                        <span>CapBay Vroom</span>
                    </a>

                    <div class="hero__copy">
                        <p class="eyebrow">Find your next drive</p>
                        <h1>
                            Start your journey with Vroom.
                        </h1>
                        <p class="hero__description">
                            Register your interest in one of our vehicles and our team will help you with the next steps.
                        </p>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="{{ route('registrations.create') }}" class="btn btn-vroom-primary btn-lg">
                            Register now
                            <span aria-hidden="true">→</span>
                        </a>

                        <span class="btn btn-vroom-disabled btn-lg disabled" aria-disabled="true">
                            Staff portal
                            <span class="status-pill">Coming soon</span>
                        </span>
                    </div>
                </section>

                <aside class="info-card col-lg-5">
                    <div class="info-card__icon" aria-hidden="true">✓</div>

                    <div>
                        <h2>Quick registration</h2>
                        <p>Choose your preferred vehicle, enter your contact details, and submit your interest in a few minutes.</p>
                    </div>

                    <ul class="feature-list">
                        <li><span></span>No account required</li>
                        <li><span></span>No online payment</li>
                        <li><span></span>Our team will contact you</li>
                    </ul>
                </aside>
                </div>
            </div>
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
