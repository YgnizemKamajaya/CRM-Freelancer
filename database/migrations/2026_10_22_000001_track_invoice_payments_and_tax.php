<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->nullable();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('invoice_payment_id')->nullable()->unique()->constrained('invoices')->nullOnDelete();
        });

        // Adopt the legacy automatically generated payment; leave manual entries intact.
        DB::table('invoices')->orderBy('id')->eachById(function ($invoice) {
            $payment = DB::table('transactions')->where('invoice_id', $invoice->id)
                ->where('type', 'income')->where('note', 'Auto dari invoice '.$invoice->invoice_number)
                ->orderBy('id')->first();
            if ($payment) {
                DB::table('transactions')->where('id', $payment->id)->update(['invoice_payment_id' => $invoice->id]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['invoice_payment_id']);
            $table->dropUnique(['invoice_payment_id']);
            $table->dropColumn('invoice_payment_id');
        });
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn('tax_percentage'));
    }
};
