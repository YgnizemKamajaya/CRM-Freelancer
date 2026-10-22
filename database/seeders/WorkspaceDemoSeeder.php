<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Budget;
use App\Models\ChecklistItem;
use App\Models\Goal;
use App\Models\JournalEntry;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

/** Demo workspace content, stable across re-seeding. */
class WorkspaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $wallet = Wallet::firstOrCreate(['name' => 'Demo · Rekening kerja'], ['type' => 'bank', 'currency' => 'IDR', 'opening_balance' => 5000000]);
        $wallet->transactions()->firstOrCreate(['note' => 'Demo · Langganan software'], ['type' => 'expense', 'category' => 'Software & tools', 'amount' => 250000, 'currency' => 'IDR', 'occurred_on' => today()]);
        Budget::firstOrCreate(['category' => 'Software & tools'], ['monthly_limit' => 500000, 'currency' => 'IDR']);
        Goal::firstOrCreate(['title' => 'Demo · Dana perangkat kerja'], ['type' => 'financial', 'target_amount' => 15000000, 'current_amount' => 0, 'auto_track' => true, 'wallet_id' => $wallet->id, 'banner_gradient' => 'slate']);
        $board = Board::firstOrCreate(['name' => 'Demo · Ide berikutnya'], ['type' => 'idea']);
        $column = $board->columns()->firstOrCreate(['name' => 'Inbox'], ['position' => 0]);
        $column->cards()->firstOrCreate(['title' => 'Rancang paket layanan baru'], ['body' => 'Pilih client saat mengubah ide menjadi project.', 'priority' => 'medium', 'position' => 0]);
        ChecklistItem::firstOrCreate(['title' => 'Demo · Tinjau prioritas hari ini'], ['for_date' => today(), 'position' => 0]);
        JournalEntry::firstOrCreate(['title' => 'Demo · Catatan awal'], ['entry_date' => today(), 'mood' => 4, 'body' => 'Apa satu hal yang ingin kamu selesaikan hari ini?', 'tags' => ['demo']]);
    }
}
