<div class="container-fluid agent-content">
    <header class="agent-page-heading">
        <div>
            <p class="eyebrow">Customer pipeline</p>
            <h1>Registrations</h1>
            <p>Search and manage every active customer journey.</p>
        </div>
    </header>

    <section class="agent-card agent-filters" aria-label="Registration filters">
        <div class="agent-filter-search">
            <label for="registration-search" class="form-label">Search</label>
            <input id="registration-search" type="search" wire:model.live.debounce.350ms="search"
                class="form-control" placeholder="Name, email, phone or registration #">
        </div>

        <div>
            <label for="status-filter" class="form-label">Status</label>
            <select id="status-filter" wire:model.live="status" class="form-select">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}" wire:key="status-{{ $statusOption->value }}">
                        {{ $statusOption->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="vehicle-filter" class="form-label">Vehicle</label>
            <select id="vehicle-filter" wire:model.live="vehicle" class="form-select">
                <option value="">All vehicles</option>
                @foreach ($vehicles as $vehicleOption)
                    <option value="{{ $vehicleOption->id }}" wire:key="vehicle-filter-{{ $vehicleOption->id }}">
                        {{ $vehicleOption->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="button" wire:click="clearFilters" class="btn btn-vroom-outline">Clear filters</button>
    </section>

    <section class="agent-card agent-table-card">
        <div class="table-responsive">
            <table class="table agent-table align-middle">
                <thead>
                    <tr>
                        <th>Registration</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Final price</th>
                        <th>Registered</th>
                        <th><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-50">
                    @forelse ($registrations as $registration)
                        <tr wire:key="registration-{{ $registration->id }}">
                            <td><strong>#{{ $registration->id }}</strong></td>
                            <td>
                                <span class="agent-customer-name">{{ $registration->customer_name }}</span>
                                <small>{{ $registration->email }}</small>
                            </td>
                            <td>{{ $registration->vehicle->name }}</td>
                            <td>
                                <span class="agent-status agent-status--{{ $registration->status }}">
                                    {{ $registration->statusEnum()->label() }}
                                </span>
                            </td>
                            <td>RM {{ number_format($registration->final_price_sen / 100, 2) }}</td>
                            <td>{{ $registration->registered_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('agent.registrations.show', $registration) }}" class="btn btn-sm btn-vroom-outline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="agent-empty">
                                <strong>No registrations found.</strong>
                                <span>Try changing or clearing the filters.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registrations->hasPages())
            @php($previousCursor = $registrations->previousCursor() ?? $registrations->cursor())
            @php($nextCursor = $registrations->nextCursor() ?? $registrations->cursor())
            <nav class="agent-pagination" aria-label="Registration pages">
                <button type="button"
                    wire:key="cursor-{{ $registrations->getCursorName() }}-{{ $previousCursor?->encode() }}"
                    wire:click="setPage('{{ $previousCursor?->encode() }}', '{{ $registrations->getCursorName() }}')"
                    class="btn btn-vroom-outline"
                    @disabled($registrations->onFirstPage())>Previous</button>
                <span>Showing up to {{ $registrations->perPage() }} registrations</span>
                <button type="button"
                    wire:key="cursor-{{ $registrations->getCursorName() }}-{{ $nextCursor?->encode() }}"
                    wire:click="setPage('{{ $nextCursor?->encode() }}', '{{ $registrations->getCursorName() }}')"
                    class="btn btn-vroom-outline"
                    @disabled(! $registrations->hasMorePages())>Next</button>
            </nav>
        @endif
    </section>
</div>
