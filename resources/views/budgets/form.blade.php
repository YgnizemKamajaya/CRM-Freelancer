@php($editing = $budget->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Budget' : 'Budget baru' }}</x-slot:title>

    <form method="POST" action="{{ $editing ? route('budgets.update', $budget) : route('budgets.store') }}" class="mx-auto max-w-xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="crm-card p-5">
            <div class="grid gap-4">
                <div>
                    <label for="category" class="crm-label">Kategori</label>
                    <input id="category" name="category" list="budget-cats" value="{{ old('category', $budget->category) }}" required class="crm-field">
                    <datalist id="budget-cats">
                        @foreach ($suggestedCategories as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                    </datalist>
                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                </div>
                <div>
                    <label for="monthly_limit" class="crm-label">Limit bulanan</label>
                    <input id="monthly_limit" name="monthly_limit" type="number" step="0.01" min="0" value="{{ old('monthly_limit', $budget->monthly_limit) }}" required class="crm-field">
                    <x-input-error :messages="$errors->get('monthly_limit')" class="mt-2" />
                </div>
                <div>
                    <label for="currency" class="crm-label">Mata uang</label>
                    <input id="currency" name="currency" value="{{ old('currency', $budget->currency ?: 'IDR') }}" maxlength="3" required class="crm-field">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div>
                @if ($editing)
                    <x-confirm-action :action="route('budgets.destroy', $budget)" label="Hapus" title="Hapus budget?" body="Tindakan ini tidak bisa dibatalkan." />
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('budgets.index') }}" class="crm-button-secondary">Batal</a>
                <button class="crm-button-primary">{{ $editing ? 'Simpan' : 'Buat budget' }}</button>
            </div>
        </div>
    </form>
</x-app-layout>
