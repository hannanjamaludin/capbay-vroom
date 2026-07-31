<?php

namespace App\Livewire\Agent;

use App\Actions\Registrations\TransitionRegistrationStatus;
use App\Actions\Registrations\UpdateRegistrationFinancials;
use App\Models\Registration;
use App\Models\User;
use App\RegistrationStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.agent')]
#[Title('Registration details')]
class RegistrationShow extends Component
{
    #[Locked]
    public Registration $registration;

    public string $downPayment = '';

    public function mount(Registration $registration): void
    {
        $this->registration = $registration->load(['vehicle', 'promotion']);
        $this->downPayment = number_format($registration->down_payment_sen / 100, 2, '.', '');
    }

    public function updateFinancials(UpdateRegistrationFinancials $updateFinancials): void
    {
        $this->authorizeAgent();

        $validated = $this->validate([
            'downPayment' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ]);

        $this->registration = $updateFinancials->handle(
            $this->registration,
            (int) round(((float) $validated['downPayment']) * 100),
        );
        $this->downPayment = number_format($this->registration->down_payment_sen / 100, 2, '.', '');
        session()->flash('agent_notice', 'Financial details updated.');
    }

    public function updateStatus(string $nextStatus, TransitionRegistrationStatus $transitionStatus): void
    {
        $this->authorizeAgent();

        $validated = validator(
            ['status' => $nextStatus],
            ['status' => ['required', Rule::enum(RegistrationStatus::class)]],
        )->validate();

        $this->registration = $transitionStatus
            ->handle($this->registration, RegistrationStatus::from($validated['status']))
            ->load(['vehicle', 'promotion']);
        session()->flash('agent_notice', 'Registration status updated.');
    }

    public function render(): View
    {
        return view('livewire.agent.registration-show', [
            'availableTransitions' => $this->registration->statusEnum()->availableTransitions(),
        ]);
    }

    private function authorizeAgent(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->isSalesAgent(), 403);
    }
}
