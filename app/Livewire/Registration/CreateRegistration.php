<?php

namespace App\Livewire\Registration;

use App\Actions\Registrations\CreateRegistrationAction;
use App\Livewire\Forms\RegistrationForm;
use App\Models\Registration;
use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Register your interest')]
class CreateRegistration extends Component
{
    public RegistrationForm $form;

    public bool $registrationComplete = false;

    public ?int $registrationNumber = null;

    public function register(CreateRegistrationAction $createRegistration): void
    {
        $this->form->normalize();
        $this->form->validate();

        $vehicle = Vehicle::query()
            ->where('is_active', true)
            ->findOrFail((int) $this->form->vehicleId);

        $registration = $createRegistration->handle(
            $vehicle,
            $this->form->registrationData(),
        );

        $this->showConfirmation($registration);
    }

    public function registerAnother(): void
    {
        $this->form->reset();
        $this->reset(['registrationComplete', 'registrationNumber']);
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.registration.create-registration', [
            'vehicles' => Vehicle::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'price_sen']),
        ]);
    }

    private function showConfirmation(Registration $registration): void
    {
        $this->registrationNumber = $registration->id;
        $this->registrationComplete = true;
    }
}
