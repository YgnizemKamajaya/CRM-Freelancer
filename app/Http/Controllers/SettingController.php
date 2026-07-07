<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'setting' => Setting::firstOrCreate(['user_id' => $request->user()->id]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_whatsapp' => ['nullable', 'string', 'max:50'],
            'business_website' => ['nullable', 'url', 'max:255'],
            'default_currency' => ['required', 'string', 'size:3'],
            'invoice_prefix' => ['required', 'string', 'max:12'],
            'invoice_next_number' => ['required', 'integer', 'min:1'],
            'default_tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $setting = Setting::firstOrCreate(['user_id' => $request->user()->id]);

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        $setting->update($data);

        return back()->with('status', 'Settings saved.');
    }
}
