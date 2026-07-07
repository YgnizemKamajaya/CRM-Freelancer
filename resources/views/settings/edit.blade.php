<x-app-layout>
    <x-slot:title>Settings</x-slot:title>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Settings</h2>
            <p class="mt-1 text-sm text-gray-500">Business identity and invoice defaults.</p>
        </div>

        <div class="crm-card grid gap-4 p-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="business_name" class="crm-label">Business name</label>
                <input id="business_name" name="business_name" value="{{ old('business_name', $setting->business_name) }}" class="crm-field">
            </div>
            <div>
                <label for="business_email" class="crm-label">Business email</label>
                <input id="business_email" name="business_email" type="email" value="{{ old('business_email', $setting->business_email) }}" class="crm-field">
            </div>
            <div>
                <label for="business_phone" class="crm-label">Business phone</label>
                <input id="business_phone" name="business_phone" value="{{ old('business_phone', $setting->business_phone) }}" class="crm-field">
            </div>
            <div>
                <label for="business_whatsapp" class="crm-label">Business WhatsApp</label>
                <input id="business_whatsapp" name="business_whatsapp" value="{{ old('business_whatsapp', $setting->business_whatsapp) }}" class="crm-field">
            </div>
            <div>
                <label for="business_website" class="crm-label">Website</label>
                <input id="business_website" name="business_website" type="url" value="{{ old('business_website', $setting->business_website) }}" class="crm-field">
            </div>
            <div class="md:col-span-2">
                <label for="logo" class="crm-label">Logo</label>
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="crm-field">
                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                @if ($setting->logo_path)
                    <div class="mt-3 flex items-center gap-3 rounded-lg border border-gray-200 p-3">
                        <img src="{{ asset('storage/'.$setting->logo_path) }}" alt="{{ $setting->business_name }} logo" class="h-12 w-12 rounded-lg object-contain">
                        <span class="text-sm text-gray-500">Current logo</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="crm-card grid gap-4 p-5 md:grid-cols-2">
            <div>
                <label for="default_currency" class="crm-label">Default currency</label>
                <input id="default_currency" name="default_currency" maxlength="3" value="{{ old('default_currency', $setting->default_currency) }}" class="crm-field">
            </div>
            <div>
                <label for="invoice_prefix" class="crm-label">Invoice prefix</label>
                <input id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $setting->invoice_prefix) }}" class="crm-field">
            </div>
            <div>
                <label for="invoice_next_number" class="crm-label">Next invoice number</label>
                <input id="invoice_next_number" name="invoice_next_number" type="number" min="1" value="{{ old('invoice_next_number', $setting->invoice_next_number) }}" class="crm-field">
            </div>
            <div>
                <label for="default_tax_percentage" class="crm-label">Default tax percentage</label>
                <input id="default_tax_percentage" name="default_tax_percentage" type="number" min="0" max="100" step="0.01" value="{{ old('default_tax_percentage', $setting->default_tax_percentage) }}" class="crm-field">
            </div>
        </div>

        <div class="crm-card p-5">
            <h3 class="font-semibold text-gray-900">PWA</h3>
            <p class="mt-2 text-sm text-gray-600">Manifest, service worker, offline page, and install metadata are already wired. Install availability depends on browser support and HTTPS in production.</p>
        </div>

        <div class="flex justify-end">
            <button class="crm-button-primary">Save Settings</button>
        </div>
    </form>
</x-app-layout>
