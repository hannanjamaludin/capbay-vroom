<x-layouts.app>
    <main class="agent-login">
        <div class="agent-login__shell">
            <section class="agent-login__intro">
                <a href="{{ route('home') }}" class="brand brand--light">
                    <span class="brand__mark brand__mark--light">V</span>
                    <span>CapBay Vroom</span>
                </a>

                <div>
                    <p class="eyebrow eyebrow--light">Sales workspace</p>
                    <h1>Keep every customer moving.</h1>
                    <p>Review registrations, update financing, and guide each customer through the sales journey.</p>
                </div>
            </section>

            <section class="agent-login__form">
                <header class="form-heading">
                    <p class="eyebrow">Agent access</p>
                    <h2>Sign in to continue</h2>
                    <p>Use your assigned sales agent account.</p>
                </header>

                <form method="POST" action="{{ route('agent.login.store') }}" class="row g-4">
                    @csrf

                    <div class="col-12">
                        <label for="email" class="form-label">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            @class(['form-control form-control-lg', 'is-invalid' => $errors->has('email')])>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            @class(['form-control form-control-lg', 'is-invalid' => $errors->has('password')])>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 form-check">
                        <input id="remember" name="remember" value="1" type="checkbox" class="form-check-input">
                        <label for="remember" class="form-check-label">Keep me signed in</label>
                    </div>

                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-vroom-primary btn-lg">Sign in</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</x-layouts.app>
