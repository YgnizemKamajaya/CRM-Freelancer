<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function show(Request $request): RedirectResponse|View
    {
        if ((bool) $request->session()->get('crm_unlocked', false)) {
            return redirect()->route('dashboard');
        }

        $configuredPin = (string) config('crm.pin');
        $pinLength = strlen($configuredPin);

        return view('auth.login', [
            'useNumpad' => ctype_digit($configuredPin) && $pinLength >= 1 && $pinLength <= 8,
            'pinLength' => $pinLength,
            'pinConfigurationValid' => $pinLength >= 1 && mb_strlen($configuredPin) <= 128,
        ]);
    }

    public function unlock(Request $request): RedirectResponse
    {
        if ((string) config('crm.pin') === '' || mb_strlen((string) config('crm.pin')) > 128) {
            throw ValidationException::withMessages(['pin' => 'CRM_PIN harus berisi 1–128 karakter. Perbarui konfigurasi workspace.']);
        }

        $data = $request->validate([
            'pin' => ['required', 'string', 'max:128'],
        ]);

        $key = 'crm-pin:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'pin' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        if (! hash_equals((string) config('crm.pin'), (string) $data['pin'])) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'pin' => 'PIN is incorrect.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('crm_unlocked', true);

        return redirect()->intended(route('dashboard'));
    }

    public function lock(Request $request): RedirectResponse
    {
        $request->session()->forget('crm_unlocked');
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Workspace locked.');
    }
}
