@php($editing = $transaction->exists)
<x-app-layout>
    <x-slot:title>{{ $editing ? 'Edit Transaksi' : 'Transaksi baru' }}</x-slot:title>

    <div x-data="{}">
        @if ($editing)
            <x-confirm-action :action="route('transactions.destroy', $transaction)" label="Hapus" title="Hapus transaksi?" body="Tindakan ini tidak bisa dibatalkan." />
        @endif

        <form method="POST" action="{{ $editing ? route('transactions.update', $transaction) : route('transactions.store') }}" class="mx-auto max-w-2xl space-y-6">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="crm-card p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="type" class="crm-label">Tipe</label>
                        <select id="type" name="type" class="crm-field">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $transaction->type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="wallet_id" class="crm-label">Wallet</label>
                        <select id="wallet_id" name="wallet_id" class="crm-field" required>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}" @selected((string) old('wallet_id', $transaction->wallet_id) === (string) $wallet->id)>{{ $wallet->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('wallet_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="amount" class="crm-label">Jumlah</label>
                        <input id="amount" name="amount" type="number" step="0.01" min="0" value="{{ old('amount', $transaction->amount) }}" required class="crm-field">
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>
                    <div>
                        <label for="occurred_on" class="crm-label">Tanggal</label>
                        <input id="occurred_on" name="occurred_on" type="date" value="{{ old('occurred_on', optional($transaction->occurred_on)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="crm-field">
                        <x-input-error :messages="$errors->get('occurred_on')" class="mt-2" />
                    </div>
                    <div>
                        <label for="category" class="crm-label">Kategori</label>
                        <input id="category" name="category" list="category-suggestions" value="{{ old('category', $transaction->category) }}" class="crm-field" placeholder="Client work, Software...">
                        <datalist id="category-suggestions">
                            @foreach ($suggestedCategories as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label for="currency" class="crm-label">Mata uang</label>
                        <input id="currency" name="currency" value="{{ old('currency', $transaction->currency ?: 'IDR') }}" maxlength="3" required class="crm-field">
                    </div>
                    <div class="md:col-span-2">
                        <label for="project_id" class="crm-label">Kaitkan ke project (opsional)</label>
                        <select id="project_id" name="project_id" class="crm-field">
                            <option value="">— Tidak ada —</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) old('project_id', $transaction->project_id) === (string) $project->id)>{{ $project->title }} @if($project->client) · {{ $project->client->name }} @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="note" class="crm-label">Catatan</label>
                        <textarea id="note" name="note" rows="3" class="crm-field">{{ old('note', $transaction->note) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div>
                    @if ($editing)
                        <button type="button" class="crm-button-danger"
                            x-on:click.prevent="$dispatch('open-modal', '{{ 'confirm-action-'.md5(route('transactions.destroy', $transaction).'Hapus'.'Hapus transaksi?') }}')">
                            Hapus
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('transactions.index') }}" class="crm-button-secondary">Batal</a>
                    <button class="crm-button-primary">{{ $editing ? 'Simpan' : 'Catat transaksi' }}</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
