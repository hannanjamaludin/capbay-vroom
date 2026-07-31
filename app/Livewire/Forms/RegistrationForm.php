<?php

namespace App\Livewire\Forms;

use App\Models\Registration;
use App\Models\Vehicle;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class RegistrationForm extends Form
{
    /**
     * @var string
     */
    public string $customerName = '';

    public string $email = '';

    public string $phone = '';

    public string $vehicleId = '';

    public string $downPayment = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customerName' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique(Registration::class, 'email'),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\+?[0-9][0-9\s().-]{7,19}$/',
                'max:20',
                Rule::unique(Registration::class, 'phone'),
            ],
            'vehicleId' => [
                'required',
                'integer',
                Rule::exists(Vehicle::class, 'id')
                    ->where(fn (Builder $query): Builder => $query->where('is_active', true)),
            ],
            'downPayment' => ['required', 'regex:/^\d+(?:\.\d{1,2})?$/'],
        ];
    }

    public function normalize(): void
    {
        $this->customerName = (string) Str::of($this->customerName)->squish();
        $this->email = (string) Str::of($this->email)->trim()->lower();
        $this->phone = preg_replace('/[^\d+]/', '', $this->phone) ?? '';
        $this->downPayment = (string) Str::of($this->downPayment)->trim();
    }

    public function downPaymentSen(): int
    {
        [$ringgit, $sen] = array_pad(explode('.', $this->downPayment, 2), 2, '');

        return ((int) $ringgit * 100) + (int) Str::padRight($sen, 2, '0');
    }

    /**
     * @return array{customer_name: string, email: string, phone: string, down_payment_sen: int}
     */
    public function registrationData(): array
    {
        return [
            'customer_name' => $this->customerName,
            'email' => $this->email,
            'phone' => $this->phone,
            'down_payment_sen' => $this->downPaymentSen(),
        ];
    }
}
