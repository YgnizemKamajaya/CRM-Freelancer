<?php

use App\Livewire\BoardShow;
use App\Livewire\HomeFocusList;
use App\Livewire\TodayChecklist;
use App\Models\Board;
use App\Models\ChecklistItem;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\FinanceService;
use App\Services\InvoiceService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Evidence probes: passing assertions confirm CURRENT defects, not correctness.
class AuditClaimValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_promotion_hits_non_nullable_client_constraint(): void
    {
        $board = Board::create(['name' => 'Probe', 'type' => 'idea']);
        $column = $board->columns()->create(['name' => 'Ideas']);
        $card = $column->cards()->create(['title' => 'Promote probe', 'priority' => 'medium']);
        $component = new BoardShow;
        $component->mount($board);
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('NOT NULL constraint failed: projects.client_id');
        $component->convertToProject($card->id);
    }

    public function test_archived_client_breaks_all_seven_claimed_routes_and_exports(): void
    {
        $client = Client::factory()->create();
        $deal = Deal::factory()->create(['client_id' => $client->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $invoice = Invoice::factory()->create(['client_id' => $client->id, 'status' => 'sent', 'amount_paid' => 0, 'due_date' => today()->subDay()]);
        $this->unlock()->delete(route('clients.destroy', $client))->assertRedirect();
        $this->assertNull($deal->fresh()->client);
        foreach (['/deals', '/deals/'.$deal->id, '/projects', '/projects/'.$project->id, '/invoices', '/invoices/'.$invoice->id, '/dashboard/crm', '/invoices/'.$invoice->id.'/print', '/invoices/'.$invoice->id.'/pdf'] as $url) {
            $response = $this->get($url);
            $this->assertSame(500, $response->status(), $url);
        }
    }

    public function test_archived_client_still_passes_relation_validation(): void
    {
        $client = Client::factory()->create();
        $client->delete();
        $this->unlock()->post(route('deals.store'), [
            'client_id' => $client->id, 'title' => 'Archived link', 'value' => 1,
            'currency' => 'IDR', 'stage' => 'new_inquiry', 'probability' => 10, 'status' => 'open',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('deals', ['client_id' => $client->id, 'title' => 'Archived link']);
    }

    public function test_date_filter_is_ignored(): void
    {
        JournalEntry::create(['entry_date' => '2026-01-01', 'title' => 'Date probe one']);
        JournalEntry::create(['entry_date' => '2026-01-02', 'title' => 'Date probe two']);
        $this->unlock()->get('/journal?date=2026-01-01')->assertOk()
            ->assertViewHas('entries', fn ($entries) => $entries->total() === 2);
    }

    public function test_client_totals_are_limited_to_eight(): void
    {
        $client = Client::factory()->create();
        Deal::factory()->count(9)->create(['client_id' => $client->id]);
        $this->unlock()->get(route('clients.show', $client))->assertOk()
            ->assertViewHas('client', fn ($model) => $model->deals->count() === 8 && $model->deals()->count() === 9);
    }

    public function test_six_invoice_items_render_five_and_form_resave_deletes_sixth(): void
    {
        $invoice = Invoice::factory()->create();
        $items = [];
        foreach (range(1, 6) as $i) {
            $items[] = ['description' => 'Probe item '.$i, 'quantity' => 1, 'unit_price' => 100];
        }
        app(InvoiceService::class)->replaceItemsAndSync($invoice, $items);
        $this->unlock()->get(route('invoices.edit', $invoice))->assertOk()
            ->assertSee('Probe item 5')->assertDontSee('Probe item 6');
        $this->put(route('invoices.update', $invoice), [
            'client_id' => $invoice->client_id, 'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date->toDateString(), 'currency' => 'IDR',
            'status' => 'sent', 'items' => array_slice($items, 0, 5),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(5, $invoice->items()->count());
        $this->assertSame('500.00', $invoice->fresh()->total);
    }

    public function test_paid_invoice_created_via_form_has_no_income_transaction(): void
    {
        $client = Client::factory()->create();
        Wallet::create(['name' => 'Probe', 'type' => 'bank', 'currency' => 'IDR']);
        $this->unlock()->post(route('invoices.store'), [
            'client_id' => $client->id, 'invoice_number' => 'PROBE-PAID',
            'issue_date' => today()->toDateString(), 'currency' => 'IDR', 'amount_paid' => 100,
            'status' => 'sent', 'items' => [['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('paid', Invoice::first()->status);
        $this->assertSame(0, Transaction::count());
    }

    public function test_paid_selection_with_zero_amount_becomes_sent(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'paid', 'amount_paid' => 0, 'due_date' => today()->addDay()]);
        app(InvoiceService::class)->replaceItemsAndSync($invoice, [['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]]);
        $this->assertSame('sent', $invoice->fresh()->status);
    }

    public function test_existing_income_is_not_reconciled_after_paid_invoice_amount_changes(): void
    {
        Wallet::create(['name' => 'Probe', 'type' => 'bank', 'currency' => 'IDR']);
        $invoice = Invoice::factory()->create(['status' => 'paid', 'amount_paid' => 100]);
        $service = app(InvoiceService::class);
        $service->replaceItemsAndSync($invoice, [['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]]);
        app(FinanceService::class)->recordInvoicePayment($invoice->refresh());
        $invoice->update(['amount_paid' => 200]);
        $service->replaceItemsAndSync($invoice, [['description' => 'Work revised', 'quantity' => 1, 'unit_price' => 200]]);
        $this->assertNull(app(FinanceService::class)->recordInvoicePayment($invoice->refresh()));
        $this->assertSame('200.00', $invoice->amount_paid);
        $this->assertSame('100.00', Transaction::first()->amount);
    }

    public function test_checklist_uncheck_leaves_task_done_in_both_components(): void
    {
        foreach ([TodayChecklist::class, HomeFocusList::class] as $class) {
            $task = Task::factory()->create(['status' => 'in_progress', 'completed_at' => null]);
            $item = ChecklistItem::create(['title' => 'Probe', 'task_id' => $task->id, 'for_date' => today()]);
            $component = new $class;
            $component->toggle($item->id);
            $component->toggle($item->id);
            $this->assertFalse($item->fresh()->is_done);
            $this->assertSame('done', $task->fresh()->status);
        }
    }

    public function test_archived_wallet_included_in_total(): void
    {
        Wallet::create(['name' => 'Active', 'currency' => 'IDR', 'opening_balance' => 100]);
        Wallet::create(['name' => 'Archived', 'currency' => 'IDR', 'opening_balance' => 200, 'is_archived' => true]);
        $this->assertSame(300.0, (float) app(FinanceService::class)->summary()['total_balance']);
    }

    public function test_seeder_second_run_adds_twenty_tasks(): void
    {
        $this->seed();
        $this->assertSame(20, Task::count());
        $this->seed();
        $this->assertSame(40, Task::count());
        $this->assertSame(0, Wallet::count());
        $this->assertSame(0, JournalEntry::count());
        $this->assertSame(0, Board::count());
    }

    public function test_six_digit_pin_is_accepted_by_backend_but_ui_remains_four(): void
    {
        config(['crm.pin' => '123456']);
        $this->get('/login')->assertOk()->assertSee('const MAX_PIN_LENGTH = 4;', false);
        $this->post('/login', ['pin' => '123456'])->assertSessionHas('crm_unlocked', true);
    }

    public function test_checklist_default_stays_at_migration_day(): void
    {
        $columns = collect(DB::select('PRAGMA table_info(checklist_items)'));
        $default = trim($columns->firstWhere('name', 'for_date')->dflt_value, "'");
        $this->travelTo(now()->addDays(7));
        $item = ChecklistItem::create(['title' => 'Default probe'])->fresh();
        $this->assertSame($default, $item->for_date->toDateString());
        $this->assertNotSame(today()->toDateString(), $item->for_date->toDateString());
        $this->travelBack();
    }

    public function test_nullable_project_fix_leaves_invoice_create_page_broken(): void
    {
        Schema::table('projects', fn ($table) => $table->foreignId('client_id')->nullable()->change());
        Project::create(['title' => 'Internal probe', 'status' => 'planned', 'priority' => 'medium']);
        $this->unlock()->get(route('invoices.create'))->assertStatus(500);
    }

    public function test_edit_wallet_has_two_delete_triggers(): void
    {
        $wallet = Wallet::create(['name' => 'Probe', 'currency' => 'IDR']);
        $html = $this->unlock()->get(route('wallets.edit', $wallet))->assertOk()->getContent();
        $this->assertSame(2, preg_match_all('/<button\b[^>]*>\s*Hapus\s*<\/button>/s', $html));
    }
}
