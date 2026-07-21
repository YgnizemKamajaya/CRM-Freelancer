<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BoardController extends Controller
{
    use ValidatesCrmRelations;

    /** Default columns seeded per board type. */
    private const DEFAULT_COLUMNS = [
        'idea' => ['Inbox', 'Someday', 'Exploring', 'Building', 'Done'],
        'project' => ['Backlog', 'To do', 'In progress', 'Review', 'Done'],
        'custom' => ['To do', 'Doing', 'Done'],
    ];

    public function index(): View
    {
        return view('workspace.index', [
            'boards' => Board::query()
                ->withCount('cards')
                ->orderBy('position')
                ->orderBy('id')
                ->get(),
            'types' => Board::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $board = DB::transaction(function () use ($data) {
            $board = Board::create($data + [
                'position' => (int) Board::max('position') + 1,
            ]);

            foreach (self::DEFAULT_COLUMNS[$board->type] ?? self::DEFAULT_COLUMNS['custom'] as $index => $name) {
                $board->columns()->create(['name' => $name, 'position' => $index]);
            }

            return $board;
        });

        return redirect()->route('workspace.show', $board)->with('status', 'Board dibuat.');
    }

    public function update(Request $request, Board $board): RedirectResponse
    {
        $this->authorizeOwner($board);
        $board->update($this->validated($request));

        return back()->with('status', 'Board diperbarui.');
    }

    public function destroy(Board $board): RedirectResponse
    {
        $this->authorizeOwner($board);
        $board->delete();

        return redirect()->route('workspace.index')->with('status', 'Board dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Board::TYPES))],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
