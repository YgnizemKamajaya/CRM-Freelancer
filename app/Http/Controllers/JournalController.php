<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JournalController extends Controller
{
    use ValidatesCrmRelations;

    public function index(Request $request, JournalService $journal): View
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $entries = JournalEntry::query()
            ->with('client')
            ->search($request->string('search')->toString())
            ->when($request->filled('mood'), fn ($query) => $query->where('mood', $request->mood))
            ->when($request->filled('tag'), fn ($query) => $query->whereJsonContains('tags', $request->string('tag')->toString()))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('entry_date', $request->input('date')))
            ->orderByDesc('entry_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('journal.index', $journal->overview() + [
            'entries' => $entries,
            'moods' => JournalEntry::MOODS,
            'moodEmoji' => JournalEntry::MOOD_EMOJI,
        ]);
    }

    public function create(Request $request): View
    {
        return view('journal.form', $this->formData() + [
            'entry' => new JournalEntry([
                'entry_date' => today(),
                'mood' => 3,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = JournalEntry::create($this->validated($request));

        return redirect()->route('journal.show', $entry)->with('status', 'Entri tersimpan.');
    }

    public function show(JournalEntry $journal): View
    {
        $this->authorizeOwner($journal);
        $journal->load(['client', 'project']);

        return view('journal.show', [
            'entry' => $journal,
            'moods' => JournalEntry::MOODS,
            'moodEmoji' => JournalEntry::MOOD_EMOJI,
        ]);
    }

    public function edit(JournalEntry $journal): View
    {
        $this->authorizeOwner($journal);

        return view('journal.form', $this->formData() + [
            'entry' => $journal,
        ]);
    }

    public function update(Request $request, JournalEntry $journal): RedirectResponse
    {
        $this->authorizeOwner($journal);
        $journal->update($this->validated($request));

        return redirect()->route('journal.show', $journal)->with('status', 'Entri diperbarui.');
    }

    public function destroy(JournalEntry $journal): RedirectResponse
    {
        $this->authorizeOwner($journal);
        $journal->delete();

        return redirect()->route('journal.index')->with('status', 'Entri dihapus.');
    }

    public function export(JournalEntry $journal): Response
    {
        $this->authorizeOwner($journal);

        $frontMatter = collect([
            'date' => $journal->entry_date->toDateString(),
            'mood' => $journal->moodLabel(),
            'tags' => collect($journal->tags)->join(', '),
        ])->filter()->map(fn ($value, $key) => "{$key}: {$value}")->implode("\n");

        $markdown = "---\n{$frontMatter}\n---\n\n# ".($journal->title ?: 'Untitled')."\n\n".($journal->body ?? '');
        $filename = $journal->entry_date->toDateString().'-'.Str::slug($journal->title ?: 'entry').'.md';

        return response($markdown, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'mood' => ['nullable', Rule::in(array_keys(JournalEntry::MOODS))],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:50000'],
            'tags' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', $this->ownedExists('clients')],
            'project_id' => ['nullable', $this->ownedExists('projects')],
        ]);

        $data['tags'] = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'moods' => JournalEntry::MOODS,
            'moodEmoji' => JournalEntry::MOOD_EMOJI,
            'clients' => $this->relationOptions(Client::class, 'client_id')->orderBy('name')->get(),
            'projects' => $this->relationOptions(Project::class, 'project_id')->with('client')->orderBy('title')->get(),
        ];
    }
}
