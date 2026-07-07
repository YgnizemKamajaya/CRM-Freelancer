<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Services\ProjectProgressService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['id' => 1],
            [
                'owner_name' => 'Demo Freelancer',
                'owner_email' => 'demo@example.com',
                'business_name' => 'Freelance CRM Studio',
                'business_email' => 'demo@example.com',
                'business_phone' => '+62 812 0000 1234',
                'business_whatsapp' => '+62 812 0000 1234',
                'business_website' => 'https://example.com',
                'default_currency' => 'IDR',
                'invoice_prefix' => 'INV',
                'invoice_next_number' => 9,
                'default_tax_percentage' => 0,
            ],
        );

        $clients = collect([
            ['name' => 'Aksara Digital', 'company_name' => 'Aksara Digital Studio', 'email' => 'hello@aksaradigital.test', 'status' => 'active_client', 'source' => 'Referral', 'tags' => ['web', 'retainer']],
            ['name' => 'Bumi Kopi', 'company_name' => 'Bumi Kopi Roastery', 'email' => 'owner@bumikopi.test', 'status' => 'prospect', 'source' => 'Instagram', 'tags' => ['ecommerce']],
            ['name' => 'Nusa Legal', 'company_name' => 'Nusa Legal Office', 'email' => 'admin@nusalegal.test', 'status' => 'active_client', 'source' => 'Website', 'tags' => ['website', 'seo']],
            ['name' => 'Ceria Course', 'company_name' => 'Ceria Course', 'email' => 'ops@ceriacourse.test', 'status' => 'lead', 'source' => 'WhatsApp', 'tags' => ['lms']],
            ['name' => 'Ruang Tenun', 'company_name' => 'Ruang Tenun', 'email' => 'founder@ruangtenun.test', 'status' => 'past_client', 'source' => 'Marketplace', 'tags' => ['catalog']],
            ['name' => 'Padi Clinic', 'company_name' => 'Padi Health Clinic', 'email' => 'contact@padiclinic.test', 'status' => 'prospect', 'source' => 'LinkedIn', 'tags' => ['booking']],
            ['name' => 'Orbit Finance', 'company_name' => 'Orbit Finance', 'email' => 'finance@orbit.test', 'status' => 'lead', 'source' => 'Referral', 'tags' => ['dashboard']],
            ['name' => 'Teras Travel', 'company_name' => 'Teras Travel', 'email' => 'sales@terastravel.test', 'status' => 'active_client', 'source' => 'Existing Client', 'tags' => ['crm']],
        ])->map(function (array $data) {
            return Client::updateOrCreate(
                ['email' => $data['email']],
                $data + [
                    'phone' => '+62 812 '.fake()->numerify('#### ####'),
                    'whatsapp' => '+62 812 '.fake()->numerify('#### ####'),
                    'website' => 'https://'.str($data['company_name'])->slug().'.test',
                    'notes' => 'Demo client with realistic CRM history.',
                    'last_contacted_at' => now()->subDays(fake()->numberBetween(1, 20)),
                ],
            );
        });

        $stageProbability = [
            'new_inquiry' => 10,
            'qualified' => 30,
            'proposal_sent' => 55,
            'negotiation' => 75,
            'won' => 100,
            'lost' => 0,
        ];

        $deals = collect([
            ['title' => 'Website redesign', 'stage' => 'negotiation', 'value' => 18000000],
            ['title' => 'Online store build', 'stage' => 'proposal_sent', 'value' => 24000000],
            ['title' => 'SEO monthly retainer', 'stage' => 'qualified', 'value' => 6500000],
            ['title' => 'Course landing page', 'stage' => 'new_inquiry', 'value' => 9500000],
            ['title' => 'Catalog refresh', 'stage' => 'won', 'value' => 12000000],
            ['title' => 'Booking system audit', 'stage' => 'proposal_sent', 'value' => 15000000],
            ['title' => 'Finance dashboard prototype', 'stage' => 'new_inquiry', 'value' => 22000000],
            ['title' => 'Travel CRM cleanup', 'stage' => 'won', 'value' => 17500000],
            ['title' => 'Support portal', 'stage' => 'lost', 'value' => 11000000],
            ['title' => 'Maintenance package', 'stage' => 'negotiation', 'value' => 8000000],
            ['title' => 'Brand website sprint', 'stage' => 'qualified', 'value' => 14000000],
            ['title' => 'Invoice automation', 'stage' => 'proposal_sent', 'value' => 10500000],
        ])->map(function (array $data, int $index) use ($clients, $stageProbability) {
            $stage = $data['stage'];

            return Deal::updateOrCreate(
                ['title' => $data['title']],
                $data + [
                    'client_id' => $clients[$index % $clients->count()]->id,
                    'currency' => 'IDR',
                    'probability' => $stageProbability[$stage],
                    'source' => array_keys(Deal::SOURCES)[$index % count(Deal::SOURCES)],
                    'status' => $stage === 'won' ? 'won' : ($stage === 'lost' ? 'lost' : 'open'),
                    'expected_close_date' => now()->addDays(7 + $index * 3)->toDateString(),
                    'next_follow_up_at' => now()->addDays($index % 6)->setHour(10),
                    'won_at' => $stage === 'won' ? now()->subDays($index + 4) : null,
                    'lost_at' => $stage === 'lost' ? now()->subDays(3) : null,
                    'lost_reason' => $stage === 'lost' ? 'Budget postponed.' : null,
                    'description' => 'Demo opportunity for portfolio walkthrough.',
                ],
            );
        });

        $projects = $deals->where('status', 'won')->values()->map(function (Deal $deal, int $index) {
            return Project::updateOrCreate(
                ['deal_id' => $deal->id],
                [
                    'client_id' => $deal->client_id,
                    'deal_id' => $deal->id,
                    'title' => $deal->title,
                    'description' => 'Delivery plan generated from won deal.',
                    'status' => $index % 2 === 0 ? 'in_progress' : 'waiting_client',
                    'priority' => $index % 2 === 0 ? 'high' : 'medium',
                    'budget' => $deal->value,
                    'currency' => 'IDR',
                    'start_date' => now()->subDays(12 - $index)->toDateString(),
                    'deadline' => now()->addDays(12 + $index * 4)->toDateString(),
                    'progress' => 35 + ($index * 20),
                ],
            );
        });

        while ($projects->count() < 5) {
            $client = $clients[$projects->count()];
            $projects->push(Project::updateOrCreate(
                ['title' => $client->company_name.' implementation'],
                [
                    'client_id' => $client->id,
                    'status' => 'planned',
                    'priority' => 'medium',
                    'budget' => 9000000 + ($projects->count() * 1500000),
                    'currency' => 'IDR',
                    'start_date' => now()->addDays(3)->toDateString(),
                    'deadline' => now()->addDays(28)->toDateString(),
                    'progress' => 10,
                ],
            ));
        }

        foreach (range(1, 20) as $i) {
            $client = $clients[$i % $clients->count()];
            $project = $projects[$i % $projects->count()];
            Task::updateOrCreate(
                ['title' => "Demo task {$i}"],
                [
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                    'title' => ['Follow up proposal', 'Prepare wireframe', 'Send invoice reminder', 'Review client feedback'][$i % 4]." {$i}",
                    'description' => 'Task seeded for daily workflow.',
                    'type' => array_keys(Task::TYPES)[$i % count(Task::TYPES)],
                    'status' => $i % 7 === 0 ? 'done' : ($i % 5 === 0 ? 'in_progress' : 'pending'),
                    'priority' => array_keys(Task::PRIORITIES)[$i % count(Task::PRIORITIES)],
                    'due_at' => now()->addDays($i - 6)->setHour(9 + ($i % 6)),
                    'completed_at' => $i % 7 === 0 ? now()->subDay() : null,
                ],
            );
        }

        $projects->each(fn (Project $project) => app(ProjectProgressService::class)->sync($project));

        foreach (range(1, 30) as $i) {
            $client = $clients[$i % $clients->count()];
            Activity::updateOrCreate(
                ['body' => "Demo activity note {$i}"],
                [
                    'client_id' => $client->id,
                    'deal_id' => $deals[$i % $deals->count()]->id,
                    'project_id' => $projects[$i % $projects->count()]->id,
                    'type' => array_keys(Activity::TYPES)[$i % count(Activity::TYPES)],
                    'body' => "Demo activity note {$i}",
                    'activity_at' => now()->subDays($i)->setHour(11),
                ],
            );
        }

        foreach (range(1, 8) as $i) {
            $client = $clients[$i % $clients->count()];
            $project = $projects[$i % $projects->count()];
            $status = ['draft', 'sent', 'partially_paid', 'paid', 'overdue', 'sent', 'paid', 'cancelled'][$i - 1];
            $total = 3500000 + ($i * 1250000);
            $invoice = Invoice::updateOrCreate(
                ['invoice_number' => sprintf('INV-%04d', $i)],
                [
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                    'issue_date' => now()->subDays(35 - ($i * 3))->toDateString(),
                    'due_date' => now()->subDays(20 - ($i * 5))->toDateString(),
                    'currency' => 'IDR',
                    'subtotal' => $total,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $total,
                    'amount_paid' => $status === 'paid' ? $total : ($status === 'partially_paid' ? $total / 2 : 0),
                    'status' => $status,
                    'notes' => 'Demo invoice.',
                    'sent_at' => $status !== 'draft' ? now()->subDays(12) : null,
                    'paid_at' => $status === 'paid' ? now()->subDays(3) : null,
                ],
            );

            $invoice->items()->updateOrCreate(
                ['description' => 'Project work package'],
                ['quantity' => 1, 'unit_price' => $total, 'total' => $total, 'sort_order' => 0],
            );
        }

        $clients->each(function (Client $client) {
            $client->update(['last_contacted_at' => $client->activities()->max('activity_at') ?? $client->last_contacted_at]);
        });
    }
}
