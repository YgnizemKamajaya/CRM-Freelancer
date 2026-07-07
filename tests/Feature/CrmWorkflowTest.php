<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_projects_index_renders_with_task_progress(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'title' => 'Client portal']);
        Task::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'project_id' => $project->id, 'status' => 'done', 'completed_at' => now()]);
        Task::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'project_id' => $project->id, 'status' => 'pending']);

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Client portal')
            ->assertSee('50%');
    }

    public function test_deal_can_be_converted_to_project_once(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Acme', 'status' => 'lead']);
        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Website build',
            'value' => 5000,
            'currency' => 'IDR',
            'stage' => 'won',
            'probability' => 100,
            'status' => 'won',
        ]);

        $this->actingAs($user)->post(route('deals.convert-to-project', $deal))->assertRedirect();
        $this->actingAs($user)->post(route('deals.convert-to-project', $deal))->assertRedirect();

        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseHas('activities', ['deal_id' => $deal->id, 'type' => 'status_update']);
    }

    public function test_invoice_items_calculate_total_and_paid_status(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Acme', 'status' => 'lead']);

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'client_id' => $client->id,
            'invoice_number' => 'INV-TEST',
            'issue_date' => today()->toDateString(),
            'due_date' => today()->addDays(7)->toDateString(),
            'currency' => 'IDR',
            'discount' => 100,
            'tax' => 50,
            'amount_paid' => 1950,
            'status' => 'sent',
            'items' => [
                ['description' => 'Build', 'quantity' => 2, 'unit_price' => 1000],
            ],
        ]);

        $response->assertRedirect();
        $invoice = Invoice::first();

        $this->assertSame('1950.00', $invoice->total);
        $this->assertSame('paid', $invoice->status);
    }

    public function test_user_cannot_open_another_users_client(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::create(['user_id' => $owner->id, 'name' => 'Private Client', 'status' => 'lead']);

        $this->actingAs($other)
            ->get(route('clients.show', $client))
            ->assertNotFound();
    }

    public function test_clients_can_be_filtered_by_tag(): void
    {
        $user = User::factory()->create();
        Client::factory()->create(['user_id' => $user->id, 'name' => 'Retainer Client', 'tags' => ['retainer']]);
        Client::factory()->create(['user_id' => $user->id, 'name' => 'One-off Client', 'tags' => ['one-off']]);

        $this->actingAs($user)
            ->get(route('clients.index', ['tag' => 'retainer']))
            ->assertOk()
            ->assertSee('Retainer Client')
            ->assertDontSee('One-off Client');
    }

    public function test_deal_stage_can_be_updated_inline(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $deal = Deal::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'stage' => 'new_inquiry',
            'status' => 'open',
            'probability' => 10,
        ]);

        $this->actingAs($user)
            ->post(route('deals.update-stage', $deal), ['stage' => 'won'])
            ->assertRedirect();

        $deal->refresh();
        $this->assertSame('won', $deal->stage);
        $this->assertSame('won', $deal->status);
        $this->assertSame(100, $deal->probability);
    }

    public function test_task_can_be_quick_rescheduled(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'done', 'completed_at' => now()]);

        $this->actingAs($user)
            ->post(route('tasks.reschedule', $task), ['preset' => 'tomorrow'])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('pending', $task->status);
        $this->assertNull($task->completed_at);
        $this->assertTrue($task->due_at->isTomorrow());
    }

    public function test_project_quick_task_returns_to_project(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'progress' => 90]);

        $this->actingAs($user)
            ->post(route('tasks.store'), [
                'context' => 'project',
                'client_id' => $client->id,
                'project_id' => $project->id,
                'title' => 'Write launch checklist',
                'type' => 'project_task',
                'status' => 'pending',
                'priority' => 'high',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Write launch checklist',
            'status' => 'pending',
        ]);
        $this->assertSame(0, $project->refresh()->progress);
    }

    public function test_project_progress_syncs_from_task_completion(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'planned',
            'progress' => 0,
        ]);
        $first = Task::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'project_id' => $project->id, 'status' => 'pending']);
        $second = Task::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'project_id' => $project->id, 'status' => 'in_progress']);
        Task::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'project_id' => $project->id, 'status' => 'done', 'completed_at' => now()]);

        $this->actingAs($user)
            ->post(route('tasks.complete', $first))
            ->assertRedirect();

        $project->refresh();
        $this->assertSame(67, $project->progress);
        $this->assertSame('in_progress', $project->status);

        $this->actingAs($user)
            ->post(route('tasks.update-status', $second), ['status' => 'done'])
            ->assertRedirect();

        $project->refresh();
        $this->assertSame(100, $project->progress);
        $this->assertSame('completed', $project->status);
        $this->assertNotNull($project->completed_at);
    }

    public function test_invoice_pdf_can_be_downloaded(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'invoice_number' => 'INV-PDF']);
        $invoice->items()->create([
            'description' => 'Design sprint',
            'quantity' => 1,
            'unit_price' => 1000,
            'total' => 1000,
        ]);

        $this->actingAs($user)
            ->get(route('invoices.pdf', $invoice))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=INV-PDF.pdf');
    }

    public function test_settings_logo_upload_is_stored(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.update'), [
                'business_name' => 'Studio',
                'business_email' => 'studio@example.com',
                'business_phone' => '',
                'business_whatsapp' => '',
                'business_website' => '',
                'default_currency' => 'IDR',
                'invoice_prefix' => 'INV',
                'invoice_next_number' => 1,
                'default_tax_percentage' => 0,
                'logo' => UploadedFile::fake()->image('logo.png', 128, 128),
            ])
            ->assertRedirect();

        $setting = Setting::where('user_id', $user->id)->firstOrFail();
        Storage::disk('public')->assertExists($setting->logo_path);
    }
}
