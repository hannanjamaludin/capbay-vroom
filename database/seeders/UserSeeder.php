<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Olivia Rodrigo',
            'email' => 'olivia@mail.com',
            'password' => Hash::make('12345678'),
            'role' => '2',
        ]);

        User::create([
            'name' => 'Albert Einstein',
            'email' => 'einstein@mail.com',
            'password' => Hash::make('12345678'),
            'role' => '2',
        ]);
    }
}
