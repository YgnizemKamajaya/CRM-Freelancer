<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ESignatureController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'signature' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:4096'],
        ]);

        $setting = Setting::firstOrCreate([]);

        if ($setting->signature_path) {
            Storage::disk('public')->delete($setting->signature_path);
        }

        $path = $request->file('signature')->store('signatures', 'public');
        $setting->update(['signature_path' => $path]);

        return response()->json([
            'ok'  => true,
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    public function destroy(): JsonResponse
    {
        $setting = Setting::first();

        if ($setting?->signature_path) {
            Storage::disk('public')->delete($setting->signature_path);
            $setting->update(['signature_path' => null]);
        }

        return response()->json(['ok' => true]);
    }
}
