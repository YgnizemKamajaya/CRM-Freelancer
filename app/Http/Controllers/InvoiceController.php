<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Setting;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    use ValidatesCrmRelations;

    public function index(Request $request): View
    {
        $invoices = Invoice::query()
            ->with(['client', 'project'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->client_id))
            ->when($request->filled('search'), fn ($query) => $query->where('invoice_number', 'like', '%'.$request->search.'%'))
            ->orderByRaw('due_date is null, due_date asc')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = Invoice::query()->get()
            ->reduce(fn (array $carry, Invoice $invoice) => [
                'unpaid' => $carry['unpaid'] + (! in_array($invoice->status, ['paid', 'cancelled'], true) ? $invoice->balance : 0),
                'overdue' => $carry['overdue'] + ($invoice->due_date?->lt(today()) && ! in_array($invoice->status, ['paid', 'cancelled'], true) ? $invoice->balance : 0),
            ], ['unpaid' => 0, 'overdue' => 0]);

        return view('invoices.index', [
            'invoices' => $invoices,
            'clients' => Client::orderBy('name')->get(),
            'statuses' => Invoice::STATUSES,
            'summary' => $summary,
        ]);
    }

    public function create(Request $request): View
    {
        $setting = Setting::firstOrCreate([]);
        $number = sprintf('%s-%04d', $setting->invoice_prefix, $setting->invoice_next_number);

        return view('invoices.form', $this->formData($request) + [
            'invoice' => new Invoice([
                'client_id' => $request->integer('client_id') ?: null,
                'project_id' => $request->integer('project_id') ?: null,
                'invoice_number' => $number,
                'issue_date' => today(),
                'currency' => $setting->default_currency,
                'status' => 'draft',
                'discount' => 0,
                'tax' => 0,
                'amount_paid' => 0,
            ]),
            'items' => collect(),
        ]);
    }

    public function store(Request $request, InvoiceService $invoiceService): RedirectResponse
    {
        $data = $this->validated($request);
        $invoice = Invoice::create(collect($data)->except('items')->all());
        $invoiceService->replaceItemsAndSync($invoice, $data['items'] ?? []);

        $setting = Setting::firstOrCreate([]);
        if (str_starts_with($invoice->invoice_number, $setting->invoice_prefix.'-')) {
            $setting->increment('invoice_next_number');
        }

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice created.');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorizeOwner($invoice);
        $invoice->load(['client', 'project', 'items', 'tasks', 'activities' => fn ($query) => $query->latest('activity_at')->latest()]);

        return view('invoices.show', [
            'invoice' => $invoice,
            'activityTypes' => Activity::TYPES,
        ]);
    }

    public function edit(Request $request, Invoice $invoice): View
    {
        $this->authorizeOwner($invoice);
        $invoice->load('items');

        return view('invoices.form', $this->formData($request) + [
            'invoice' => $invoice,
            'items' => $invoice->items,
        ]);
    }

    public function update(Request $request, Invoice $invoice, InvoiceService $invoiceService): RedirectResponse
    {
        $this->authorizeOwner($invoice);
        $data = $this->validated($request, $invoice);
        $invoice->update(collect($data)->except('items')->all());
        $invoiceService->replaceItemsAndSync($invoice, $data['items'] ?? []);

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice updated.');
    }

    public function markSent(Invoice $invoice, InvoiceService $invoiceService): RedirectResponse
    {
        $this->authorizeOwner($invoice);
        $invoiceService->markSent($invoice);

        return back()->with('status', 'Invoice marked sent.');
    }

    public function markPaid(Invoice $invoice, InvoiceService $invoiceService): RedirectResponse
    {
        $this->authorizeOwner($invoice);
        $invoiceService->markPaid($invoice);

        return back()->with('status', 'Invoice marked paid.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorizeOwner($invoice);
        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Invoice deleted.');
    }

    public function print(Invoice $invoice): View
    {
        $this->authorizeOwner($invoice);
        $invoice->load(['client', 'project', 'items']);

        return view('invoices.print', ['invoice' => $invoice, 'setting' => Setting::first()]);
    }

    public function pdf(Invoice $invoice): Response
    {
        $this->authorizeOwner($invoice);
        $invoice->load(['client', 'project', 'items']);

        return Pdf::loadView('invoices.pdf', ['invoice' => $invoice, 'setting' => Setting::first()])
            ->setPaper('a4')
            ->download($invoice->invoice_number.'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Invoice $invoice = null): array
    {
        return $request->validate([
            'client_id' => ['required', $this->ownedExists('clients')],
            'project_id' => ['nullable', $this->ownedExists('projects')],
            'invoice_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('invoices', 'invoice_number')->ignore($invoice),
            ],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', 'string', 'size:3'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(Invoice::STATUSES))],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        return [
            'clients' => Client::orderBy('name')->get(),
            'projects' => Project::with('client')->orderBy('title')->get(),
            'statuses' => Invoice::STATUSES,
        ];
    }
}
