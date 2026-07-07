@php
    $money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.');
    $setting = $invoice->user->setting;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .header { width: 100%; margin-bottom: 36px; }
        .title { font-size: 28px; font-weight: bold; margin: 0; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        .grid { width: 100%; margin-bottom: 28px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #6b7280; border-bottom: 1px solid #d1d5db; padding: 10px 0; font-size: 10px; text-transform: uppercase; }
        td { border-bottom: 1px solid #e5e7eb; padding: 10px 0; }
        .totals { width: 260px; margin-left: auto; margin-top: 24px; }
        .totals td { border: 0; padding: 4px 0; }
        .total-row td { border-top: 1px solid #d1d5db; font-size: 16px; font-weight: bold; padding-top: 10px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <p class="title">Invoice</p>
                <p class="muted">{{ $invoice->invoice_number }}</p>
            </td>
            <td class="right">
                <strong>{{ $setting?->business_name ?? 'Freelance CRM Studio' }}</strong><br>
                <span class="muted">{{ $setting?->business_email }}</span><br>
                <span class="muted">{{ $setting?->business_phone }}</span>
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td>
                <span class="muted">Bill to</span><br>
                <strong>{{ $invoice->client->name }}</strong><br>
                <span class="muted">{{ $invoice->client->email }}</span>
            </td>
            <td class="right">
                Issue: {{ $invoice->issue_date->format('d M Y') }}<br>
                Due: {{ $invoice->due_date?->format('d M Y') ?? '-' }}<br>
                Status: {{ str_replace('_', ' ', str($invoice->status)->title()) }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $money($item->unit_price) }}</td>
                    <td class="right">{{ $money($item->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ $money($invoice->subtotal) }}</td></tr>
        <tr><td>Discount</td><td class="right">{{ $money($invoice->discount) }}</td></tr>
        <tr><td>Tax</td><td class="right">{{ $money($invoice->tax) }}</td></tr>
        <tr class="total-row"><td>Total</td><td class="right">{{ $money($invoice->total) }}</td></tr>
    </table>
</body>
</html>
