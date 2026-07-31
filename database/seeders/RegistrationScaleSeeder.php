<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\RegistrationStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationScaleSeeder extends Seeder
{
    private const int RegistrationCount = 50_000;

    private const int ChunkSize = 1_000;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = $this->vehicles();
        $seededAt = now()->startOfSecond();
        $rows = [];

        foreach (range(1, self::RegistrationCount) as $registrationNumber) {
            $rows[] = $this->registrationRow($registrationNumber, $vehicles, $seededAt);

            if (count($rows) === self::ChunkSize) {
                $this->upsert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            $this->upsert($rows);
        }

        $this->command?->info(sprintf(
            '%s scale registrations are ready.',
            number_format(self::RegistrationCount),
        ));
    }

    /**
     * @return list<Vehicle>
     */
    private function vehicles(): array
    {
        $vehicleFixtures = [
            ['name' => 'CapBay Vroom', 'price_sen' => 20_000_000],
            ['name' => 'CapBay Vroom City', 'price_sen' => 8_800_000],
            ['name' => 'CapBay Vroom Sport', 'price_sen' => 15_500_000],
            ['name' => 'CapBay Vroom Family', 'price_sen' => 13_900_000],
            ['name' => 'CapBay Vroom Electric', 'price_sen' => 18_750_000],
        ];

        return array_map(
            fn (array $vehicle): Vehicle => Vehicle::query()->firstOrCreate(
                ['name' => $vehicle['name']],
                ['price_sen' => $vehicle['price_sen'], 'is_active' => true],
            ),
            $vehicleFixtures,
        );
    }

    /**
     * @param  list<Vehicle>  $vehicles
     * @return array<string, int|string|null|CarbonInterface>
     */
    private function registrationRow(int $registrationNumber, array $vehicles, CarbonInterface $seededAt): array
    {
        $vehicle = $vehicles[($registrationNumber - 1) % count($vehicles)];
        $registeredAt = $seededAt->copy()->subMinutes($registrationNumber * 15);
        $status = $this->statusFor($registrationNumber);
        $downPaymentSen = (int) ($vehicle->price_sen * (10 + ($registrationNumber % 31)) / 100);

        $testDriveScheduledAt = in_array($status, [
            RegistrationStatus::TestDriveScheduled,
            RegistrationStatus::TestDriveCompleted,
            RegistrationStatus::Purchased,
        ], true)
            ? $registeredAt->copy()->addDays(2)
            : null;
        $testDriveCompletedAt = in_array($status, [
            RegistrationStatus::TestDriveCompleted,
            RegistrationStatus::Purchased,
        ], true)
            ? $registeredAt->copy()->addDays(3)
            : null;
        $purchasedAt = $status === RegistrationStatus::Purchased
            ? $registeredAt->copy()->addDays(5)
            : null;

        return [
            'vehicle_id' => $vehicle->id,
            'promotion_id' => null,
            'customer_name' => sprintf('Scale Customer %05d', $registrationNumber),
            'email' => sprintf('scale.registration.%05d@example.test', $registrationNumber),
            'phone' => sprintf('019-%07d', $registrationNumber),
            'status' => $status->value,
            'registered_at' => $registeredAt,
            'test_drive_scheduled_at' => $testDriveScheduledAt,
            'test_drive_completed_at' => $testDriveCompletedAt,
            'down_payment_sen' => $downPaymentSen,
            'paid_down_payment' => true,
            'vehicle_price_sen' => $vehicle->price_sen,
            'applied_discount_sen' => 0,
            'final_price_sen' => $vehicle->price_sen,
            'loan_amount_sen' => $vehicle->price_sen - $downPaymentSen,
            'purchased_at' => $purchasedAt,
            'cancelled_at' => $status === RegistrationStatus::Cancelled
                ? $registeredAt->copy()->addDay()
                : null,
            'created_at' => $registeredAt,
            'updated_at' => $seededAt,
        ];
    }

    private function statusFor(int $registrationNumber): RegistrationStatus
    {
        return match ($registrationNumber % 20) {
            0, 1 => RegistrationStatus::Cancelled,
            2, 3 => RegistrationStatus::Purchased,
            4, 5, 6 => RegistrationStatus::TestDriveCompleted,
            7, 8, 9, 10 => RegistrationStatus::TestDriveScheduled,
            default => RegistrationStatus::Registered,
        };
    }

    /**
     * @param  list<array<string, int|string|null|CarbonInterface>>  $rows
     */
    private function upsert(array $rows): void
    {
        DB::table('registrations')->upsert(
            $rows,
            ['email'],
            [
                'vehicle_id',
                'promotion_id',
                'customer_name',
                'phone',
                'status',
                'registered_at',
                'test_drive_scheduled_at',
                'test_drive_completed_at',
                'down_payment_sen',
                'paid_down_payment',
                'vehicle_price_sen',
                'applied_discount_sen',
                'final_price_sen',
                'loan_amount_sen',
                'purchased_at',
                'cancelled_at',
                'updated_at',
            ],
        );
    }
}
