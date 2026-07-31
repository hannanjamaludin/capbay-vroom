<main class="registration-page">
    <div class="registration-shell row g-0">
        <section class="registration-intro col-lg-5">
            <a href="{{ route('home') }}" class="brand brand--light" aria-label="Back to CapBay Vroom home">
                <span class="brand__mark brand__mark--light">V</span>
                <span>CapBay Vroom</span>
            </a>

            <div class="registration-intro__copy">
                <p class="eyebrow eyebrow--light">Your next drive starts here</p>
                <h1>Register your interest.</h1>
                <p>Tell us which vehicle caught your eye. Our team will contact you about the next steps.</p>
            </div>

            <p class="registration-intro__note">No payment is collected through this form.</p>
        </section>

        <section class="registration-form-panel col-lg-7">
            @if ($registrationComplete)
                <div class="confirmation" role="status">
                    <div class="confirmation__icon">✓</div>
                    <div>
                        <p class="eyebrow">Registration received</p>
                        <h2>Thank you for registering.</h2>
                        <p>
                            Your registration number is <strong>#{{ $registrationNumber }}</strong>.
                            Our team will be in touch soon.
                        </p>
                    </div>
                    <button type="button" wire:click="registerAnother" class="btn btn-vroom-outline btn-lg">
                        Submit another registration
                    </button>
                </div>
            @else
                <header class="form-heading">
                    <p class="eyebrow">Customer details</p>
                    <h2>Let’s get you registered</h2>
                    <p>All fields are required.</p>
                </header>

                <form wire:submit="register" class="row g-4" novalidate>
                    <div class="col-12">
                        <label for="customer-name" class="form-label">Full name</label>
                        <input id="customer-name" type="text" wire:model.blur="form.customerName" autocomplete="name"
                            @class(['form-control form-control-lg', 'is-invalid' => $errors->has('form.customerName')])
                            placeholder="Your full name">
                        @error('form.customerName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" type="email" wire:model.blur="form.email" autocomplete="email"
                                @class(['form-control form-control-lg', 'is-invalid' => $errors->has('form.email')])
                                placeholder="you@example.com">
                            @error('form.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                            <label for="phone" class="form-label">Phone number</label>
                            <input id="phone" type="tel" wire:model.blur="form.phone" autocomplete="tel"
                                @class(['form-control form-control-lg', 'is-invalid' => $errors->has('form.phone')])
                                placeholder="012-3456789">
                            @error('form.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="vehicle" class="form-label">Preferred vehicle</label>
                        <select id="vehicle" wire:model="form.vehicleId"
                            @class(['form-select form-select-lg', 'is-invalid' => $errors->has('form.vehicleId')])>
                            <option value="">Select a vehicle</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" wire:key="vehicle-{{ $vehicle->id }}">
                                    {{ $vehicle->name }} — RM {{ number_format($vehicle->price_sen / 100, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('form.vehicleId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="down-payment" class="form-label">Planned down payment (RM)</label>
                        <input id="down-payment" type="text" inputmode="decimal" wire:model.blur="form.downPayment"
                            @class(['form-control form-control-lg', 'is-invalid' => $errors->has('form.downPayment')])
                            placeholder="10000.00">
                        @error('form.downPayment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 d-grid">
                        <button type="submit" wire:loading.attr="disabled" class="btn btn-vroom-primary btn-lg">
                            <span wire:loading.remove wire:target="register">Register now</span>
                            <span wire:loading wire:target="register">Submitting…</span>
                        </button>
                    </div>
                </form>
            @endif
        </section>
    </div>
</main>
