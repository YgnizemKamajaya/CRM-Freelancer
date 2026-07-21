<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Goal;
use App\Models\Wallet;
use App\Services\FinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GoalController extends Controller
{
    use ValidatesCrmRelations;

    // TODO(audit V7): goal banner image upload/serve can't be tested statically
    //   (no vendor; PHP upload limits are env-specific). Verify uploads land in
    //   storage/app/goal-banners (private) and the banner route is PIN-gated.
    //   See AUDIT-TODO.md.

    private const BANNER_DIR = 'goal-banners';

    public function index(FinanceService $finance): View
    {
        $goals = Goal::query()
            ->where('is_archived', false)
            ->with('wallet')
            ->orderByDesc('is_pinned')
            ->orderBy('type')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Goal $goal) use ($finance) {
                $goal->live_amount = $finance->goalCurrentAmount($goal);
                $goal->live_percent = $goal->percent($goal->live_amount);

                return $goal;
            });

        return view('goals.index', [
            'goals' => $goals,
            'pinnedGoals' => $goals->where('is_pinned', true)->values(),
        ]);
    }

    public function create(): View
    {
        return view('goals.form', $this->formData() + [
            'goal' => new Goal(['type' => 'personal', 'current_amount' => 0, 'banner_gradient' => 'slate']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['banner_path'] = $this->storeBanner($request);

        Goal::create($data);

        return redirect()->route('goals.index')->with('status', 'Goal dibuat.');
    }

    public function edit(Goal $goal): View
    {
        $this->authorizeOwner($goal);

        return view('goals.form', $this->formData() + ['goal' => $goal]);
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $this->authorizeOwner($goal);

        $data = $this->validated($request);

        if ($newPath = $this->storeBanner($request)) {
            $this->deleteBanner($goal);
            $data['banner_path'] = $newPath;
        } elseif ($request->boolean('remove_banner')) {
            $this->deleteBanner($goal);
            $data['banner_path'] = null;
        }

        $goal->update($data);

        return redirect()->route('goals.index')->with('status', 'Goal diperbarui.');
    }

    public function adjust(Request $request, Goal $goal): RedirectResponse
    {
        $this->authorizeOwner($goal);

        $data = $request->validate(['delta' => ['required', 'numeric']]);

        $goal->update([
            'current_amount' => max(0, (float) $goal->current_amount + (float) $data['delta']),
        ]);

        return back()->with('status', 'Progress diperbarui.');
    }

    public function togglePin(Goal $goal): RedirectResponse
    {
        $this->authorizeOwner($goal);
        $goal->update(['is_pinned' => ! $goal->is_pinned]);

        return back()->with('status', $goal->is_pinned ? 'Goal dipin.' : 'Pin dilepas.');
    }

    public function banner(Goal $goal): StreamedResponse
    {
        $this->authorizeOwner($goal);

        abort_unless($goal->banner_path && Storage::disk('local')->exists($goal->banner_path), 404);

        return Storage::disk('local')->response($goal->banner_path);
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $this->authorizeOwner($goal);
        $goal->delete(); // deleting hook removes the banner image

        return redirect()->route('goals.index')->with('status', 'Goal dihapus.');
    }

    private function storeBanner(Request $request): ?string
    {
        if (! $request->hasFile('banner_image')) {
            return null;
        }

        return $request->file('banner_image')->store(self::BANNER_DIR, 'local');
    }

    private function deleteBanner(Goal $goal): void
    {
        if ($goal->banner_path && Storage::disk('local')->exists($goal->banner_path)) {
            Storage::disk('local')->delete($goal->banner_path);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Goal::TYPES))],
            'metric_label' => ['nullable', 'string', 'max:50'],
            'banner_gradient' => ['nullable', Rule::in(array_keys(Goal::GRADIENTS))],
            'banner_image' => ['nullable', 'image', 'max:8192'], // 8 MB
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'wallet_id' => ['nullable', $this->ownedExists('wallets')],
            'auto_track' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
            'is_pinned' => ['sometimes', 'boolean'],
        ]);

        // auto_track only makes sense for financial goals.
        if (($data['type'] ?? null) !== 'financial') {
            $data['auto_track'] = false;
            $data['wallet_id'] = null;
        }

        // banner_image is handled separately; never mass-assign it.
        unset($data['banner_image']);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'types' => Goal::TYPES,
            'gradients' => Goal::GRADIENTS,
            'wallets' => Wallet::where('is_archived', false)->orderBy('name')->get(),
        ];
    }
}
