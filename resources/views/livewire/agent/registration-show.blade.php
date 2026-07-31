<div class="container-fluid agent-content">
    <a href="{{ route('agent.registrations.index') }}" class="agent-back">← Back to registrations</a>

    <header class="agent-page-heading agent-page-heading--detail">
        <div>
            <p class="eyebrow">Registration #{{ $registration->id }}</p>
            <h1>{{ $registration->customer_name }}</h1>
            <p>{{ $registration->vehicle->name }} · Registered {{ $registration->registered_at->format('d M Y, g:i A') }}</p>
        </div>
        <span class="agent-status agent-status--{{ $registration->status }}">
            {{ $registration->statusEnum()->label() }}
        </span>
    </header>

    @if (session('agent_notice'))
        <div class="agent-notice" role="status">{{ session('agent_notice') }}</div>
    @endif

    <div class="agent-detail-grid">
        <div class="agent-detail-main">
            <section class="agent-card">
                <header class="agent-section-heading">
                    <div>
                        <p class="eyebrow">Pricing summary</p>
                        <h2>Financial details</h2>
                    </div>
                    <span @class(['eligibility-badge', 'eligibility-badge--eligible' => $registration->isPromotionEligible()])>
                        {{ $registration->isPromotionEligible() ? 'Promotion eligible' : 'No promotion applied' }}
                    </span>
                </header>

                <dl class="price-grid">
                    <div><dt>Vehicle price</dt><dd>RM {{ number_format($registration->vehicle_price_sen / 100, 2) }}</dd></div>
                    <div><dt>Discount</dt><dd>− RM {{ number_format($registration->applied_discount_sen / 100, 2) }}</dd></div>
                    <div class="price-grid__highlight"><dt>Final price</dt><dd>RM {{ number_format($registration->final_price_sen / 100, 2) }}</dd></div>
                    <div class="price-grid__highlight"><dt>Loan amount</dt><dd>RM {{ number_format($registration->loan_amount_sen / 100, 2) }}</dd></div>
                </dl>

                @if ($registration->promotion)
                    <p class="agent-muted">Applied promotion: <strong>{{ $registration->promotion->name }}</strong></p>
                @endif

                <form wire:submit="updateFinancials" class="financial-form">
                    <div>
                        <label for="down-payment" class="form-label">Down payment (RM)</label>
                        <input id="down-payment" type="number" min="0" step="0.01" wire:model="downPayment"
                            @class(['form-control', 'is-invalid' => $errors->has('downPayment')])>
                        @error('downPayment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-vroom-primary">
                        Update financials
                    </button>
                </form>

            </section>

            <section class="agent-card">
                <header class="agent-section-heading">
                    <div>
                        <p class="eyebrow">Customer journey</p>
                        <h2>Status controls</h2>
                    </div>
                </header>

                @error('status') <div class="alert alert-danger">{{ $message }}</div> @enderror

                @if ($availableTransitions !== [])
                    <div class="status-actions">
                        @foreach ($availableTransitions as $nextStatus)
                            <button type="button" wire:click="updateStatus('{{ $nextStatus->value }}')"
                                wire:key="transition-{{ $nextStatus->value }}"
                                @class(['btn', 'btn-vroom-primary' => $nextStatus->value !== 'cancelled', 'btn-agent-danger' => $nextStatus->value === 'cancelled'])>
                                Mark as {{ strtolower($nextStatus->label()) }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="agent-muted mb-0">This registration has reached a final status.</p>
                @endif
            </section>
        </div>

        <aside class="agent-detail-side">
            <section class="agent-card">
                <p class="eyebrow">Customer</p>
                <dl class="detail-list">
                    <div><dt>Email</dt><dd><a href="mailto:{{ $registration->email }}">{{ $registration->email }}</a></dd></div>
                    <div><dt>Phone</dt><dd><a href="tel:{{ $registration->phone }}">{{ $registration->phone }}</a></dd></div>
                    <div><dt>Vehicle</dt><dd>{{ $registration->vehicle->name }}</dd></div>
                </dl>
            </section>

            <section class="agent-card">
                <p class="eyebrow">Milestones</p>
                <dl class="detail-list">
                    <div><dt>Test drive scheduled</dt><dd>{{ $registration->test_drive_scheduled_at?->format('d M Y, g:i A') ?? 'Not yet' }}</dd></div>
                    <div><dt>Test drive completed</dt><dd>{{ $registration->test_drive_completed_at?->format('d M Y, g:i A') ?? 'Not yet' }}</dd></div>
                    <div><dt>Purchased</dt><dd>{{ $registration->purchased_at?->format('d M Y, g:i A') ?? 'Not yet' }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
</div>
