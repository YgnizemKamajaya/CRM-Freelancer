<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ESignatureController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'signature' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:4096'],
        ]);

        $setting = Setting::firstOrCreate([]);

        $previousPath = $setting->signature_path;
        $path = $request->file('signature')->store('signatures', 'public');
        if (! $path) {
            throw ValidationException::withMessages([
                'signature' => 'Gambar belum tersimpan. Coba lagi; tanda tangan lama tetap tersedia.',
            ]);
        }
        try {
            $setting->update(['signature_path' => $path]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
        if ($previousPath && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return response()->json([
            'ok' => true,
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
