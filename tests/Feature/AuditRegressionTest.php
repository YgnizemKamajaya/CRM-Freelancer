<?php

namespace Tests\Feature;

use App\Livewire\BoardShow;
use App\Livewire\HomeFocusList;
use App\Livewire\QuickCapture;
use App\Livewire\TodayChecklist;
use App\Models\Board;
use App\Models\ChecklistItem;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\FinanceService;
use Database\Seeders\WorkspaceDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function invoiceData(array $overrides = []): array
    {
        return array_replace([
            'client_id' => Client::factory()->create()->id,
            'invoice_number' => 'REG-'.uniqid(),
            'issue_date' => today()->toDateString(), 'currency' => 'IDR',
            'status' => 'sent', 'amount_paid' => 0, 'discount' => 0, 'tax' => 0,
            'items' => [['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]],
        ], $overrides);
    }

    public function test_invoice_edit_keeps_all_rows_and_validation_input(): void
    {
        $data = $this->invoiceData(['items' => array_map(fn ($i) => ['description' => 'Row '.$i, 'quantity' => 1, 'unit_price' => 100], range(1, 7))]);
        $this->unlock()->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $this->get(route('invoices.edit', $invoice))->assertOk()->assertSee('Row 7');
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertSame(7, $invoice->items()->count());
        $this->assertSame('700.00', $invoice->fresh()->total);
        $data['currency'] = '';
        $this->from(route('invoices.edit', $invoice))->put(route('invoices.update', $invoice), $data)
            ->assertSessionHasErrors('currency')->assertSessionHasInput('items.6.description', 'Row 7');
        $this->get(route('invoices.edit', $invoice))->assertOk()->assertSee('Row 7');
        $this->assertSame(7, $invoice->items()->count());
    }

    public function test_percentage_tax_is_applied_after_discount_and_can_be_cleared(): void
    {
        Setting::create(['default_tax_percentage' => 11]);
        $this->unlock()->get(route('invoices.create'))->assertOk()->assertViewHas('invoice', fn ($invoice) => $invoice->tax_percentage === '11.00');
        $data = $this->invoiceData(['discount' => 10, 'tax_percentage' => 11]);
        $this->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $invoice = Invoice::first();
        $this->assertSame('9.90', $invoice->tax);
        $this->assertSame('99.90', $invoice->total);
        $data['tax_percentage'] = null;
        $data['tax'] = 5;
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertSame('95.00', $invoice->fresh()->total);
    }

    public function test_paid_create_update_and_cancel_reconcile_exactly_one_income(): void
    {
        Wallet::create(['name' => 'IDR', 'currency' => 'IDR']);
        $data = $this->invoiceData(['amount_paid' => 100]);
        $this->unlock()->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('100.00', Transaction::sole()->amount);
        $data['items'][0]['unit_price'] = 200;
        $data['amount_paid'] = 200;
        $data['status'] = 'paid';
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertSame('200.00', Transaction::sole()->amount);
        $this->assertSame($invoice->id, Transaction::sole()->invoice_payment_id);
        $data['status'] = 'cancelled';
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_partial_payment_reductions_and_reversal_are_reconciled(): void
    {
        Wallet::create(['name' => 'IDR', 'currency' => 'IDR']);
        $data = $this->invoiceData(['amount_paid' => 40]);
        $this->unlock()->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertSame('40.00', Transaction::sole()->amount);
        $data['amount_paid'] = 20;
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertSame('20.00', Transaction::sole()->amount);
        $data['amount_paid'] = 0;
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_invoice_deletion_removes_managed_income_and_keeps_manual_entries(): void
    {
        $wallet = Wallet::create(['name' => 'IDR', 'currency' => 'IDR']);
        $data = $this->invoiceData(['amount_paid' => 100]);
        $this->unlock()->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $invoice = Invoice::first();
        $manual = Transaction::create(['wallet_id' => $wallet->id, 'invoice_id' => $invoice->id, 'type' => 'expense', 'amount' => 10, 'currency' => 'IDR', 'occurred_on' => today()]);
        $this->delete(route('invoices.destroy', $invoice))->assertRedirect();
        $this->assertSoftDeleted($invoice);
        $this->assertSame($manual->id, Transaction::sole()->id);
    }

    public function test_manual_income_is_not_double_counted_and_currency_matches_wallet(): void
    {
        Wallet::create(['name' => 'USD first', 'currency' => 'USD']);
        $wallet = Wallet::create(['name' => 'IDR second', 'currency' => 'IDR']);
        $invoice = Invoice::factory()->create(['amount_paid' => 100, 'status' => 'paid', 'currency' => 'IDR']);
        $manual = Transaction::create(['wallet_id' => $wallet->id, 'invoice_id' => $invoice->id, 'type' => 'income', 'amount' => 30, 'currency' => 'IDR', 'occurred_on' => today()]);
        app(FinanceService::class)->recordInvoicePayment($invoice);
        $this->assertSame(2, Transaction::count());
        $auto = Transaction::whereNotNull('invoice_payment_id')->sole();
        $this->assertSame('70.00', $auto->amount);
        $this->assertSame($wallet->id, $auto->wallet_id);
        $this->assertSame('30.00', $manual->fresh()->amount);
    }

    public function test_managed_transactions_cannot_be_edited_or_deleted_independently(): void
    {
        Wallet::create(['name' => 'IDR', 'currency' => 'IDR']);
        $data = $this->invoiceData(['amount_paid' => 100]);
        $this->unlock()->post(route('invoices.store'), $data);
        $transaction = Transaction::sole();
        $this->get(route('transactions.edit', $transaction))->assertRedirect(route('invoices.edit', $transaction->invoice_payment_id));
        $this->put(route('transactions.update', $transaction), [])->assertStatus(409);
        $this->delete(route('transactions.destroy', $transaction))->assertStatus(409);
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_invalid_paid_selection_rolls_back_header_items_and_number(): void
    {
        $data = $this->invoiceData(['status' => 'paid', 'amount_paid' => 0]);
        $this->unlock()->post(route('invoices.store'), $data)->assertSessionHasErrors('amount_paid');
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('invoice_items', 0);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_missing_matching_wallet_is_visible_and_resave_records_payment(): void
    {
        Wallet::create(['name' => 'USD', 'currency' => 'USD']);
        $data = $this->invoiceData(['amount_paid' => 100]);
        $this->unlock()->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $invoice = Invoice::first();
        $this->assertDatabaseCount('transactions', 0);
        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('Pembayaran belum seluruhnya tercatat');
        Wallet::create(['name' => 'IDR', 'currency' => 'IDR']);
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $this->assertSame('100.00', Transaction::sole()->amount);
    }

    public function test_archiving_client_keeps_lists_details_selectors_and_exports_readable(): void
    {
        $client = Client::factory()->create();
        $deal = Deal::factory()->create(['client_id' => $client->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $invoice = Invoice::factory()->create(['client_id' => $client->id, 'status' => 'sent', 'amount_paid' => 0]);
        $this->unlock()->delete(route('clients.destroy', $client));
        foreach (['/deals', '/deals/'.$deal->id, '/projects', '/projects/'.$project->id, '/invoices', '/invoices/'.$invoice->id, '/dashboard/crm', '/invoices/'.$invoice->id.'/print', '/invoices/'.$invoice->id.'/pdf', '/invoices/create', '/projects/create'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('deals.show', $deal))->assertSee('(diarsipkan)')->assertDontSee('href="'.route('clients.show', $client).'"', false);
        $this->get(route('invoices.edit', $invoice))->assertOk()->assertViewHas('clients', fn ($clients) => $clients->contains('id', $client->id));
    }

    public function test_archived_relation_can_be_retained_but_not_newly_selected(): void
    {
        $client = Client::factory()->create();
        $invoice = Invoice::factory()->create(['client_id' => $client->id, 'amount_paid' => 0, 'status' => 'sent']);
        $client->delete();
        $data = $this->invoiceData(['client_id' => $client->id, 'invoice_number' => $invoice->invoice_number]);
        $this->unlock()->put(route('invoices.update', $invoice), $data)->assertSessionHasNoErrors();
        $data['invoice_number'] = 'NEW-ARCHIVED';
        $this->post(route('invoices.store'), $data)->assertSessionHasErrors('client_id');
        $other = Client::factory()->create();
        $other->delete();
        $data['client_id'] = $other->id;
        $this->put(route('invoices.update', $invoice), $data)->assertSessionHasErrors('client_id');
    }

    public function test_client_aggregates_are_complete_and_exclude_cancelled_invoices(): void
    {
        $client = Client::factory()->create();
        Deal::factory()->count(9)->create(['client_id' => $client->id]);
        Invoice::factory()->count(9)->create(['client_id' => $client->id, 'total' => 100, 'amount_paid' => 25, 'status' => 'partially_paid']);
        Invoice::factory()->create(['client_id' => $client->id, 'total' => 999, 'amount_paid' => 0, 'status' => 'cancelled']);
        $this->unlock()->get(route('clients.show', $client))->assertOk()->assertViewHas('client', fn ($record) => $record->deals_count === 9 && (float) $record->invoiced_total === 900.0 && (float) $record->outstanding_total === 675.0);
    }

    public function test_board_promotion_requires_active_client_is_atomic_and_idempotent(): void
    {
        $board = Board::create(['name' => 'Board', 'type' => 'idea']);
        $column = $board->columns()->create(['name' => 'Ideas']);
        $card = $column->cards()->create(['title' => 'Promote', 'priority' => 'medium']);
        $client = Client::factory()->create();
        $this->unlock();
        $component = Livewire::test(BoardShow::class, ['board' => $board]);
        $component->call('convertToProject', $card->id)->assertHasErrors('promotionClientId');
        $this->assertDatabaseCount('projects', 0);
        $component->set('promotionClientId', $client->id)->call('convertToProject', $card->id)->assertHasNoErrors();
        $component->call('convertToProject', $card->id)->assertHasNoErrors();
        $this->assertDatabaseCount('projects', 1);
        $this->assertSame($client->id, Project::sole()->client_id);
        $this->assertSame(Project::sole()->id, $card->fresh()->project_id);
    }

    public function test_archived_client_is_not_eligible_for_card_promotion(): void
    {
        $board = Board::create(['name' => 'Board']);
        $card = $board->columns()->create(['name' => 'Ideas'])->cards()->create(['title' => 'Promote']);
        $client = Client::factory()->create();
        $client->delete();
        $this->unlock();
        Livewire::test(BoardShow::class, ['board' => $board])->set('promotionClientId', $client->id)->call('convertToProject', $card->id)->assertHasErrors('promotionClientId');
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_journal_date_filter_applies_and_rejects_invalid_dates(): void
    {
        JournalEntry::create(['entry_date' => '2026-01-01', 'title' => 'One']);
        JournalEntry::create(['entry_date' => '2026-01-02', 'title' => 'Two']);
        $this->unlock()->get('/journal?date=2026-01-01')->assertOk()->assertViewHas('entries', fn ($entries) => $entries->total() === 1 && $entries->first()->title === 'One');
        $this->getJson('/journal?date=2026-99-99')->assertUnprocessable();
    }

    public function test_numeric_and_passphrase_pin_modes_unlock_without_leaking_secret(): void
    {
        foreach (['0042', '123456', 'work passphrase', '123456789012', ' spaced pin '] as $pin) {
            $this->flushSession();
            config(['crm.pin' => $pin]);
            $response = $this->get('/login')->assertOk();
            if (ctype_digit($pin) && strlen($pin) <= 8) {
                $response->assertSee('const MAX_PIN_LENGTH = '.strlen($pin).';', false);
            } else {
                $response->assertSee('id="workspace-pin"', false);
            }
            $this->post('/login', ['pin' => $pin])->assertSessionHas('crm_unlocked', true);
        }
    }

    public function test_invalid_pin_configuration_and_throttling_are_reported(): void
    {
        config(['crm.pin' => str_repeat('x', 129)]);
        $this->get('/login')->assertOk()->assertSee('Perbarui konfigurasi workspace');
        $this->post('/login', ['pin' => '1234'])->assertSessionHasErrors('pin');
        config(['crm.pin' => '1234']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['pin' => '0000'])->assertSessionHasErrors('pin');
        }
        $this->postJson('/login', ['pin' => '1234'])->assertUnprocessable()->assertSee('Too many attempts');
    }

    public function test_checklist_completion_updates_project_and_uncheck_does_not_override_task(): void
    {
        foreach ([TodayChecklist::class, HomeFocusList::class] as $componentClass) {
            $project = Project::factory()->create(['status' => 'planned']);
            $task = Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress']);
            $item = ChecklistItem::create(['title' => 'Linked', 'task_id' => $task->id]);
            $component = new $componentClass;
            $component->toggle($item->id);
            $this->assertSame('done', $task->fresh()->status);
            $this->assertSame(100, $project->fresh()->progress);
            $component->toggle($item->id);
            $this->assertFalse($item->fresh()->is_done);
            $this->assertSame('done', $task->fresh()->status);
        }
    }

    public function test_checklist_default_uses_creation_date_and_date_navigation_persists(): void
    {
        $this->travelTo(now()->addWeek());
        $item = ChecklistItem::create(['title' => 'Today']);
        $this->assertSame(today()->toDateString(), $item->fresh()->for_date->toDateString());
        $this->travelBack();
        $this->unlock();
        Livewire::test(TodayChecklist::class, ['date' => '2026-01-01'])->call('changeDate', 1)->assertSet('date', '2026-01-02');
        $this->get('/today?date=2026-01-02')->assertOk();
    }

    public function test_duplicate_delete_triggers_are_removed_on_workspace_forms(): void
    {
        $wallet = Wallet::create(['name' => 'Wallet', 'currency' => 'IDR']);
        $html = $this->unlock()->get(route('wallets.edit', $wallet))->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<button\b[^>]*>\s*Hapus\s*<\/button>/s', $html));
    }

    public function test_seeding_twice_does_not_duplicate_tasks(): void
    {
        $this->seed();
        $count = Task::count();
        $this->seed();
        $this->assertSame($count, Task::count());
    }

    public function test_archived_wallet_assets_are_preserved_in_totals_and_linked_goal(): void
    {
        $wallet = Wallet::create(['name' => 'Archived', 'currency' => 'IDR', 'opening_balance' => 200, 'is_archived' => true]);
        $goal = new Goal(['auto_track' => true, 'type' => 'financial', 'wallet_id' => $wallet->id]);
        $this->assertSame(200.0, (float) app(FinanceService::class)->summary()['total_balance']);
        $this->assertSame(200.0, app(FinanceService::class)->goalCurrentAmount($goal));
    }

    public function test_board_actions_and_task_deletion_are_reachable(): void
    {
        $board = Board::create(['name' => 'Old', 'type' => 'idea']);
        $this->unlock()->get(route('workspace.show', $board))->assertOk()->assertSee('Edit board')->assertSee('Hapus board');
        $this->put(route('workspace.update', $board), ['name' => 'New', 'type' => 'custom'])->assertSessionHasNoErrors();
        $this->assertSame('New', $board->fresh()->name);
        $this->delete(route('workspace.destroy', $board))->assertRedirect(route('workspace.index'));
        $task = Task::factory()->create();
        $this->get(route('tasks.edit', $task))->assertOk()->assertSee('Delete task');
        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
    }

    public function test_payment_migration_adopts_legacy_income_and_preserves_it_on_rollback(): void
    {
        $migration = require database_path('migrations/2026_10_22_000001_track_invoice_payments_and_tax.php');
        $migration->down();
        $wallet = Wallet::create(['name' => 'Legacy', 'currency' => 'IDR']);
        $invoice = Invoice::factory()->create(['invoice_number' => 'LEGACY', 'amount_paid' => 100, 'status' => 'paid']);
        $payment = Transaction::create(['wallet_id' => $wallet->id, 'invoice_id' => $invoice->id, 'type' => 'income', 'amount' => 100, 'currency' => 'IDR', 'occurred_on' => today(), 'note' => 'Auto dari invoice LEGACY']);
        $migration->up();
        $this->assertSame($invoice->id, $payment->fresh()->invoice_payment_id);
        app(FinanceService::class)->recordInvoicePayment($invoice);
        $this->assertDatabaseCount('transactions', 1);
        $migration->down();
        $this->assertSame('100.00', $payment->fresh()->amount);
        $migration->up();
    }

    public function test_workspace_demo_seeding_is_idempotent_and_searchable(): void
    {
        $this->seed(WorkspaceDemoSeeder::class);
        $this->seed(WorkspaceDemoSeeder::class);
        $this->assertDatabaseCount('wallets', 1);
        $this->assertDatabaseCount('boards', 1);
        $this->assertDatabaseCount('goals', 1);
        $this->assertDatabaseCount('journal_entries', 1);
        $this->unlock();
        Livewire::test(QuickCapture::class)->set('query', 'Demo')
            ->assertSee('Demo · Ide berikutnya')->assertSee('Demo · Dana perangkat kerja');
    }

    public function test_line_item_amounts_are_rounded_before_tax(): void
    {
        $data = $this->invoiceData(['items' => [['description' => 'Fraction', 'quantity' => 0.1, 'unit_price' => 0.05]]]);
        $this->unlock()->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
        $this->assertSame('0.01', Invoice::sole()->total);
    }
}
