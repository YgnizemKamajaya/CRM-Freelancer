<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Unlock workspace</h1>
        <p class="mt-2 text-sm text-gray-500">Enter your private CRM PIN.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="pin" value="PIN" />
            <x-text-input id="pin" class="block mt-1 w-full text-center text-2xl tracking-[0.35em]" type="password" name="pin" required autofocus inputmode="numeric" autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('pin')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button>
                Unlock
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
