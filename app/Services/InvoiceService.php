<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function replaceItemsAndSync(Invoice $invoice, array $items): Invoice
    {
        return DB::transaction(function () use ($invoice, $items) {
            $invoice->items()->delete();

            collect($items)
                ->filter(fn (array $item) => filled(Arr::get($item, 'description')))
                ->values()
                ->each(function (array $item, int $index) use ($invoice) {
                    $quantity = (float) ($item['quantity'] ?? 1);
                    $unitPrice = (float) ($item['unit_price'] ?? 0);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $quantity * $unitPrice,
                        'sort_order' => $index,
                    ]);
                });

            return $this->syncTotals($invoice->refresh());
        });
    }

    public function syncTotals(Invoice $invoice): Invoice
    {
        $subtotal = (float) $invoice->items()->sum('total');
        $discount = (float) $invoice->discount;
        $tax = (float) $invoice->tax;
        $total = max(0, $subtotal - $discount + $tax);
        $amountPaid = min((float) $invoice->amount_paid, $total);

        $invoice->forceFill([
            'subtotal' => $subtotal,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'status' => $this->statusFor($invoice, $amountPaid, $total),
            'paid_at' => $amountPaid >= $total && $total > 0 ? ($invoice->paid_at ?? now()) : null,
        ])->save();

        return $invoice;
    }

    public function markSent(Invoice $invoice): Invoice
    {
        $invoice->forceFill([
            'status' => 'sent',
            'sent_at' => $invoice->sent_at ?? now(),
        ])->save();

        return $this->syncTotals($invoice);
    }

    public function markPaid(Invoice $invoice): Invoice
    {
        $invoice->forceFill([
            'amount_paid' => $invoice->total,
            'status' => 'paid',
            'paid_at' => now(),
        ])->save();

        return $invoice;
    }

    private function statusFor(Invoice $invoice, float $amountPaid, float $total): string
    {
        if ($invoice->status === 'cancelled') {
            return 'cancelled';
        }

        if ($total > 0 && $amountPaid >= $total) {
            return 'paid';
        }

        if ($amountPaid > 0) {
            return 'partially_paid';
        }

        if ($invoice->due_date?->lt(today()) && ! in_array($invoice->status, ['draft', 'paid'], true)) {
            return 'overdue';
        }

        return $invoice->status === 'draft' ? 'draft' : 'sent';
    }
}
