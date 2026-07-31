<?php

namespace App\Livewire\Agent;

use App\Models\Registration;
use App\Models\Vehicle;
use App\RegistrationStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.agent')]
#[Title('Sales registrations')]
class RegistrationIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $vehicle = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'vehicle'], true)) {
            $this->resetPage('cursor');
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'vehicle']);
        $this->resetPage('cursor');
    }

    public function render(): View
    {
        $search = trim($this->search);

        return view('livewire.agent.registration-index', [
            'registrations' => Registration::query()
                ->select([
                    'id', 'vehicle_id', 'customer_name', 'email', 'status',
                    'final_price_sen', 'registered_at',
                ])
                ->with('vehicle:id,name')
                ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->when($this->vehicle !== '', fn ($query) => $query->where('vehicle_id', $this->vehicle))
                ->orderByDesc('id')
                ->cursorPaginate(12),
            'statuses' => RegistrationStatus::cases(),
            'vehicles' => Vehicle::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** @param Builder<Registration> $query */
    private function applySearch(Builder $query, string $search): void
    {
        if (filter_var($search, FILTER_VALIDATE_EMAIL) !== false) {
            $query->where('email', $search);

            return;
        }

        if (preg_match('/^\+?[0-9 ()-]{7,}$/', $search) === 1) {
            $query->where('phone', $search);

            return;
        }

        $query->where(function (Builder $query) use ($search): void {
            $query->where('customer_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");

            if (ctype_digit($search)) {
                $query->orWhere('id', $search);
            }
        });
    }
}
