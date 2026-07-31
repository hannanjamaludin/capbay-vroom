<?php

use App\Livewire\Registration\CreateRegistration;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/register', CreateRegistration::class)->name('registrations.create');
