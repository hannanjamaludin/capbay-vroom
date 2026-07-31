<?php

use App\Http\Controllers\Auth\SalesAgentSessionController;
use App\Livewire\Agent\RegistrationIndex;
use App\Livewire\Agent\RegistrationShow;
use App\Livewire\Registration\CreateRegistration;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/register', CreateRegistration::class)->name('registrations.create');

Route::middleware('guest')->group(function (): void {
    Route::get('/agent/login', [SalesAgentSessionController::class, 'create'])->name('agent.login');
    Route::post('/agent/login', [SalesAgentSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('agent.login.store');
});

Route::middleware(['auth', 'sales-agent'])->prefix('agent')->name('agent.')->group(function (): void {
    Route::livewire('/registrations', RegistrationIndex::class)->name('registrations.index');
    Route::livewire('/registrations/{registration}', RegistrationShow::class)->name('registrations.show');
    Route::post('/logout', [SalesAgentSessionController::class, 'destroy'])->name('logout');
});
