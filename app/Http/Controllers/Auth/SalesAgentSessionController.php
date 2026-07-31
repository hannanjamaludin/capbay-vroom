<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SalesAgentLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalesAgentSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User && $user->isSalesAgent()) {
            return redirect()->route('agent.registrations.index');
        }

        return view('auth.sales-agent-login');
    }

    public function store(SalesAgentLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        return redirect()->intended(route('agent.registrations.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('agent.login');
    }
}
