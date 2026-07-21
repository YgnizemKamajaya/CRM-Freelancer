<x-app-layout>
    <x-slot:title>Focus</x-slot:title>

    <div
        x-data="pomodoro()"
        x-init="init()"
        class="personal-page focus-room mx-auto max-w-2xl"
    >
        <div class="personal-hero">
            <div class="min-w-0">
                <p class="crm-eyebrow">Personal</p>
                <h2 class="text-2xl font-semibold" style="color: var(--crm-heading);">Focus</h2>
                <p class="mt-1 text-sm" style="color: var(--crm-muted);">Teknik Pomodoro — fokus, lalu istirahat.</p>
            </div>
            <div class="personal-hero-side">
                <div class="personal-mascot is-focus" aria-hidden="true">
                    <span class="mascot-ear left"></span><span class="mascot-ear right"></span>
                    <span class="mascot-head"><span class="mascot-eye left"></span><span class="mascot-eye right"></span><span class="mascot-mouth"></span></span>
                    <span class="mascot-body"></span><span class="mascot-shadow"></span>
                </div>
            </div>
        </div>

        <div class="crm-card personal-panel p-8 text-center">
            <div class="mb-4 flex items-center justify-center gap-2">
                <template x-for="m in modes" :key="m.key">
                    <button
                        @click="switchMode(m.key)"
                        class="pomodoro-mode"
                        :class="mode === m.key ? 'is-active' : ''"
                        x-text="m.label"
                    ></button>
                </template>
            </div>

            <div class="pomodoro-clock" :class="running ? 'is-running' : ''" x-text="display"></div>

            <p class="mt-2 text-sm" style="color: var(--crm-subtle);">
                <span x-text="mode === 'work' ? 'Sesi fokus' : 'Waktu istirahat'"></span>
                · <span x-text="completed"></span> sesi selesai
            </p>

            <input
                x-show="mode === 'work'"
                x-model="taskLabel"
                class="crm-field mx-auto mt-4 max-w-xs text-center"
                placeholder="Lagi ngerjain apa?"
            >

            <div class="mt-6 flex items-center justify-center gap-3">
                <button @click="toggle()" class="crm-button-primary" style="min-width: 8rem;">
                    <span x-text="running ? 'Jeda' : 'Mulai'"></span>
                </button>
                <button @click="reset()" class="crm-button-secondary">Reset</button>
            </div>
        </div>

        <div class="crm-card personal-panel p-5">
            <h3 class="mb-3 text-sm font-semibold" style="color: var(--crm-heading);">Pengaturan</h3>
            <div class="grid grid-cols-3 gap-3">
                <label class="text-xs" style="color: var(--crm-muted);">
                    Fokus (menit)
                    <input type="number" min="1" max="90" x-model.number="durations.work" @change="applyDurations()" class="crm-field">
                </label>
                <label class="text-xs" style="color: var(--crm-muted);">
                    Istirahat pendek
                    <input type="number" min="1" max="30" x-model.number="durations.short" @change="applyDurations()" class="crm-field">
                </label>
                <label class="text-xs" style="color: var(--crm-muted);">
                    Istirahat panjang
                    <input type="number" min="1" max="60" x-model.number="durations.long" @change="applyDurations()" class="crm-field">
                </label>
            </div>
            <label class="mt-3 flex items-center gap-2 text-sm" style="color: var(--crm-text);">
                <input type="checkbox" x-model="notify" @change="maybeRequestNotify()" class="h-4 w-4">
                Beri tahu saat sesi selesai
            </label>
            <p class="mt-3 text-xs" style="color: var(--crm-subtle);">
                {{-- TODO(future): to persist focus sessions, dispatch to a backend Livewire action here (schema focus_sessions). See AUDIT-TODO.md. --}}
                Sesi tidak disimpan — timer hanya berjalan di layar ini.
            </p>
        </div>
    </div>

    <script>
        function pomodoro() {
            return {
                modes: [
                    { key: 'work', label: 'Fokus' },
                    { key: 'short', label: 'Istirahat' },
                    { key: 'long', label: 'Panjang' },
                ],
                durations: { work: 25, short: 5, long: 15 },
                mode: 'work',
                remaining: 25 * 60,
                running: false,
                completed: 0,
                taskLabel: '',
                notify: false,
                timer: null,
                init() {
                    this.remaining = this.durations[this.mode] * 60;
                },
                get display() {
                    const m = String(Math.floor(this.remaining / 60)).padStart(2, '0');
                    const s = String(this.remaining % 60).padStart(2, '0');
                    return `${m}:${s}`;
                },
                applyDurations() {
                    if (! this.running) this.remaining = this.durations[this.mode] * 60;
                },
                switchMode(key) {
                    this.mode = key;
                    this.stop();
                    this.remaining = this.durations[key] * 60;
                },
                toggle() {
                    this.running ? this.stop() : this.start();
                },
                start() {
                    this.running = true;
                    this.timer = setInterval(() => {
                        if (this.remaining > 0) {
                            this.remaining--;
                        } else {
                            this.finish();
                        }
                    }, 1000);
                },
                stop() {
                    this.running = false;
                    clearInterval(this.timer);
                },
                reset() {
                    this.stop();
                    this.remaining = this.durations[this.mode] * 60;
                },
                finish() {
                    this.stop();
                    this.beep();
                    if (this.mode === 'work') {
                        this.completed++;
                        this.sendNotify('Sesi fokus selesai!', 'Saatnya istirahat.');
                    } else {
                        this.sendNotify('Istirahat selesai!', 'Kembali fokus.');
                    }
                    // Auto-advance: work -> short break, break -> work.
                    this.switchMode(this.mode === 'work' ? 'short' : 'work');
                },
                beep() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = 660;
                        gain.gain.setValueAtTime(0.15, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.35);
                    } catch (e) { /* audio not available */ }
                },
                maybeRequestNotify() {
                    if (this.notify && 'Notification' in window && Notification.permission === 'default') {
                        Notification.requestPermission();
                    }
                },
                sendNotify(title, body) {
                    if (this.notify && 'Notification' in window && Notification.permission === 'granted') {
                        new Notification(title, { body });
                    }
                },
            };
        }
    </script>
</x-app-layout>
