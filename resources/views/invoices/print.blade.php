@php
    $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.');
    $setting = $setting ?? \App\Models\Setting::first();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->invoice_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900">
    <main class="mx-auto max-w-4xl p-8">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold">Invoice</h1>
                <p class="mt-2 text-gray-500">{{ $invoice->invoice_number }}</p>
            </div>
            <div class="text-right">
                @if ($setting?->logo_path)
                    <img src="{{ asset('storage/'.$setting->logo_path) }}" alt="" class="ml-auto mb-3 h-12 w-12 object-contain">
                @endif
                <p class="font-semibold">{{ $setting?->business_name ?? 'Freelance CRM Studio' }}</p>
                <p class="text-sm text-gray-500">{{ $setting?->business_email }}</p>
            </div>
        </div>
        <section class="mt-10 grid gap-8 md:grid-cols-2">
            <div>
                <p class="text-xs font-medium uppercase text-gray-500">Bill to</p>
                <p class="mt-2 font-semibold">{{ $invoice->client->name }}</p>
                <p class="text-sm text-gray-500">{{ $invoice->client->email }}</p>
            </div>
            <div class="md:text-right">
                <p class="text-sm">Issue: {{ $invoice->issue_date->format('d M Y') }}</p>
                <p class="text-sm">Due: {{ $invoice->due_date?->format('d M Y') ?? '-' }}</p>
            </div>
        </section>
        <table class="mt-10 w-full text-left text-sm">
            <thead class="border-b border-gray-300 text-xs uppercase text-gray-500">
                <tr><th class="py-3">Description</th><th class="py-3">Qty</th><th class="py-3">Unit</th><th class="py-3 text-right">Total</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($invoice->items as $item)
                    <tr><td class="py-3">{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $money($item->unit_price) }}</td><td class="text-right">{{ $money($item->total) }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="ml-auto mt-8 max-w-xs space-y-2 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span>{{ $money($invoice->subtotal) }}</span></div>
            <div class="flex justify-between"><span>Discount</span><span>{{ $money($invoice->discount) }}</span></div>
            <div class="flex justify-between"><span>Tax</span><span>{{ $money($invoice->tax) }}</span></div>
            <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-bold"><span>Total</span><span>{{ $money($invoice->total) }}</span></div>
        </div>

        @if($setting?->signature_path)
        <div class="mt-12 flex flex-col items-end gap-2">
            <div class="rounded border border-gray-200 p-2" style="background-image: repeating-conic-gradient(#d1d5db 0% 25%, #f9fafb 0% 50%) 0 0 / 12px 12px;">
                <img src="{{ asset('storage/' . $setting->signature_path) }}" alt="Tanda Tangan" class="max-h-20 max-w-[200px] object-contain">
            </div>
            <p class="text-xs text-gray-500 border-t border-gray-200 pt-1">{{ $setting->owner_name ?? 'Freelance CRM' }}</p>
        </div>
        @endif
    </main>
</body>
</html>
