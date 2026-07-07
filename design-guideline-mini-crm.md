# Design Guideline — Mini CRM for Freelancer

**Versi:** 1.0
**Stack asumsi:** Tailwind CSS + Flowbite components, HeroIcons (outline/solid), React/Vue/Blade (framework-agnostic)
**Target:** WCAG 2.1 Level AA

---

## 1. Design Principles

1. **Clarity over decoration** — setiap elemen visual harus punya fungsi. Kalau tidak menambah pemahaman, hilangkan.
2. **Low visual noise** — palet pastel, kontras terkontrol, whitespace generous. Hindari gradient berlebihan, shadow tebal, atau warna-warni tanpa makna semantik.
3. **Predictable patterns** — komponen yang sama (button, badge, table row action) selalu terlihat & berperilaku sama di semua halaman.
4. **Accessible by default**, bukan tempelan — ARIA, kontras, dan keyboard nav dipikirkan dari awal komponen dibuat, bukan ditambal belakangan.
5. **Data-first layout** — karena ini CRM, tabel/list adalah elemen utama. Layout harus memprioritaskan scan-ability data (nama klien, status project, invoice due date) dibanding ornamen.

---

## 2. Color Palette (Pastel, Flowbite-based)

Gunakan skala warna Tailwind/Flowbite tapi **dibatasi di shade 50–200 untuk background**, dan **500–700 untuk teks/ikon aksen** supaya kontrasnya tetap WCAG AA compliant meskipun terkesan pastel.

### 2.1 Neutral (basis UI)
| Token | Hex | Penggunaan |
|---|---|---|
| `gray-50` | #F9FAFB | Page background |
| `gray-100` | #F3F4F6 | Card background alternatif, table stripe |
| `gray-200` | #E5E7EB | Border, divider |
| `gray-400` | #9CA3AF | Placeholder text, disabled icon |
| `gray-500` | #6B7280 | Secondary text |
| `gray-700` | #374151 | Body text |
| `gray-900` | #111827 | Heading text |

### 2.2 Brand / Primary (pastel blue — trust, professional)
| Token | Hex | Penggunaan |
|---|---|---|
| `primary-50` | #EFF6FF | Background badge/alert ringan |
| `primary-100` | #DBEAFE | Hover background item aktif |
| `primary-500` | #3B82F6 | Primary button, link, active state |
| `primary-600` | #2563EB | Primary button hover |
| `primary-700` | #1D4ED8 | Primary button pressed/focus-visible ring |

### 2.3 Semantic (status project/invoice — tetap soft, bukan neon)
| Status | Token | Hex | Contoh Penggunaan |
|---|---|---|---|
| Success | `green-50` / `green-600` | #ECFDF5 / #059669 | Invoice Paid, Project Completed |
| Warning | `amber-50` / `amber-600` | #FFFBEB / #D97706 | Invoice Due Soon, Pending Review |
| Danger | `rose-50` / `rose-600` | #FFF1F2 | Invoice Overdue, Project Cancelled |
| Info | `sky-50` / `sky-600` | #F0F9FF / #0284C7 | Draft, Info tooltip |
| Neutral | `gray-50` / `gray-600` | — | Archived, Inactive Client |

> **Catatan aksesibilitas:** shade `-600` ke atas WAJIB dipakai untuk teks di atas background `-50`, supaya rasio kontras ≥ 4.5:1 (AA untuk teks normal). Jangan pernah pakai `-400`/`-500` sebagai warna teks di atas background putih/pastel — kontrasnya sering gagal AA.

### 2.4 Dark mode (opsional, siapkan token-nya dari awal)
Gunakan `dark:bg-gray-900`, `dark:bg-gray-800` untuk card, `dark:text-gray-100` untuk heading, `dark:text-gray-400` untuk secondary. Semantic color tetap pakai shade `-400` di dark mode agar tetap kontras di background gelap (mis. `dark:text-green-400`).

---

## 3. Typography

Font: **Inter** atau **system-ui** (Flowbite default), sans-serif, mendukung legibility tinggi di ukuran kecil (table cell).

| Level | Size / Line-height | Weight | Tailwind class | Penggunaan |
|---|---|---|---|---|
| Display | 30px / 36px | 700 | `text-3xl font-bold` | Judul halaman utama (mis. "Dashboard") |
| H2 | 24px / 32px | 600 | `text-2xl font-semibold` | Section header (mis. "Recent Invoices") |
| H3 | 18px / 28px | 600 | `text-lg font-semibold` | Card title |
| Body | 14px / 20px | 400 | `text-sm` | Isi tabel, form label content |
| Small | 12px / 16px | 400 | `text-xs` | Caption, timestamp, helper text |

**Aturan:**
- Minimal ukuran teks interaktif (button/link) 14px, jangan lebih kecil dari itu untuk elemen yang bisa diklik.
- Line-height minimal 1.5x untuk body text (WCAG 1.4.8).
- Kontras teks vs background minimal 4.5:1 (teks normal), 3:1 (teks besar ≥18px/bold ≥14px).

---

## 4. Spacing & Layout Grid

- Base unit: **4px** (Tailwind default). Gunakan kelipatan: `p-2` (8px), `p-4` (16px), `p-6` (24px) untuk padding card/section.
- Sidebar width: `w-64` (256px) desktop, collapsible ke icon-only `w-16` di tablet.
- Content max-width: `max-w-7xl`, dengan gutter `px-4 md:px-6 lg:px-8`.
- Card radius: `rounded-lg` (8px) — konsisten di semua card, modal, dan input.
- Grid dashboard: 12-column grid, stat card 3–4 kolom di desktop, stack penuh di mobile (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`).

---

## 5. Iconography (HeroIcons)

- **Sumber:** [heroicons.com](https://heroicons.com) — gunakan variant **Outline** untuk state default/inactive, **Solid** untuk state aktif/selected (mis. sidebar menu aktif).
- **Ukuran standar:**
  - `w-5 h-5` (20px) — icon di dalam button, table row action, form input adorment.
  - `w-6 h-6` (24px) — sidebar navigation icon.
  - `w-4 h-4` (16px) — icon kecil di badge/status chip.
- **Warna icon** mengikuti warna teks di sekitarnya (`currentColor`), jangan hardcode warna icon terpisah dari konteks teks.
- **Aksesibilitas icon:**
  - Icon **dekoratif** (menemani teks yang sudah jelas, mis. icon di sebelah label "Clients"): beri `aria-hidden="true"`.
  - Icon **fungsional tanpa teks** (mis. tombol edit/hapus icon-only di tabel): WAJIB `aria-label`, contoh:
    ```html
    <button aria-label="Edit client">
      <svg aria-hidden="true" class="w-5 h-5">...</svg>
    </button>
    ```
  - Jangan pernah biarkan icon-only button tanpa label — screen reader akan membacanya sebagai "button" tanpa konteks.

**Rekomendasi icon per fitur CRM:**
| Fitur | Icon (Outline) |
|---|---|
| Dashboard | `HomeIcon` |
| Clients | `UsersIcon` |
| Projects | `BriefcaseIcon` / `FolderIcon` |
| Invoices | `DocumentTextIcon` |
| Tasks | `CheckCircleIcon` |
| Reports | `ChartBarIcon` |
| Settings | `Cog6ToothIcon` |
| Notifications | `BellIcon` |
| Search | `MagnifyingGlassIcon` |
| Add new | `PlusIcon` |
| Edit | `PencilSquareIcon` |
| Delete | `TrashIcon` |
| Due date | `CalendarDaysIcon` |
| Paid/Success | `CheckCircleIcon` (solid, green-600) |
| Overdue/Danger | `ExclamationTriangleIcon` (solid, rose-600) |

---

## 6. Core Components (Flowbite pattern)

### 6.1 Button
```html
<!-- Primary -->
<button class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg
  hover:bg-primary-700 focus:ring-4 focus:ring-primary-200
  focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
  Simpan
</button>

<!-- Secondary / Outline -->
<button class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200
  rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-100">
  Batal
</button>
```
- Selalu sediakan `focus:ring` / `focus-visible:outline` — jangan hilangkan outline default tanpa pengganti (kegagalan WCAG 2.4.7 yang paling sering terjadi).
- Ukuran target sentuh minimal **44x44px** (WCAG 2.5.5) untuk tombol di mobile.

### 6.2 Status Badge
```html
<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
  bg-green-50 text-green-600">
  <svg aria-hidden="true" class="w-3 h-3">...</svg>
  Paid
</span>
```

### 6.3 Table (data utama CRM)
- Header kolom pakai `<th scope="col">`.
- Row action (edit/delete) dikelompokkan di kolom terakhir, icon-only + `aria-label`.
- Sortable column: tombol header punya `aria-sort="ascending|descending|none"`.
- Empty state wajib ada (lihat §8).
- Zebra striping opsional (`odd:bg-white even:bg-gray-50`) untuk scan-ability, bukan wajib kalau border sudah jelas.

### 6.4 Form Input
```html
<label for="client-name" class="block mb-1 text-sm font-medium text-gray-700">
  Nama Klien
</label>
<input id="client-name" type="text" required
  aria-describedby="client-name-error"
  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg
  focus:ring-2 focus:ring-primary-200 focus:border-primary-500" />
<p id="client-name-error" class="mt-1 text-xs text-rose-600" role="alert">
  Nama klien wajib diisi.
</p>
```
- Setiap `<input>` punya `<label>` terhubung via `for`/`id` (jangan hanya placeholder).
- Error message terhubung via `aria-describedby`, dan container error pakai `role="alert"` supaya diumumkan screen reader.

### 6.5 Modal / Dialog
- `role="dialog"` `aria-modal="true"` `aria-labelledby="modal-title"`.
- Focus trap: fokus otomatis ke elemen pertama saat modal terbuka, kembali ke trigger button saat ditutup.
- Bisa ditutup dengan `Esc`.

### 6.6 Sidebar Navigation
- `<nav aria-label="Main navigation">`.
- Item aktif ditandai dengan `aria-current="page"`, bukan cuma warna berbeda.
- Icon `w-6 h-6`, label teks selalu ada (jangan icon-only di desktop sidebar).

---

## 7. Accessibility Checklist (WCAG 2.1 AA)

- [ ] Kontras teks ≥ 4.5:1 (normal), ≥ 3:1 (large text/UI component) — cek pakai axe DevTools / Contrast Checker.
- [ ] Semua elemen interaktif bisa diakses via keyboard (`Tab`, `Enter`, `Space`, `Esc`).
- [ ] Focus indicator selalu terlihat (`focus-visible`), tidak dihapus dengan `outline-none` tanpa pengganti.
- [ ] Semua icon-only button punya `aria-label`.
- [ ] Semua form input punya `<label>` terkoneksi, error via `aria-describedby` + `role="alert"`.
- [ ] Heading hierarchy logis (`h1` → `h2` → `h3`, tidak loncat level).
- [ ] Gambar/avatar informatif punya `alt`, dekoratif punya `alt=""`.
- [ ] Status warna (badge) tidak mengandalkan warna saja — sertakan icon/teks (mis. "Overdue" + ikon warning, bukan cuma merah).
- [ ] Modal punya focus trap dan bisa ditutup dengan `Esc`.
- [ ] Tabel besar tetap bisa dinavigasi via keyboard, kolom header pakai `scope="col"`.
- [ ] Ukuran target sentuh interaktif minimal 44x44px di mobile.
- [ ] Reduce motion dihormati (`prefers-reduced-motion`) untuk animasi transisi.

---

## 8. States & Edge Cases

- **Empty state**: ilustrasi/icon simple (mis. `FolderIcon` besar, `text-gray-300`) + copy singkat + CTA ("Belum ada klien. Tambah klien pertama Anda.").
- **Loading state**: skeleton loader dengan `bg-gray-100 animate-pulse`, bukan spinner besar yang mengganggu untuk tabel.
- **Error state**: pesan jelas + tombol retry, warna `rose-600` teks di atas `rose-50` background.
- **Empty search result**: bedakan dari empty state kosong data ("Tidak ditemukan hasil untuk 'xxx'").

---

## 9. Halaman Inti Mini CRM (referensi layout)

| Halaman | Komponen utama |
|---|---|
| Dashboard | Stat cards (Total Klien, Project Aktif, Invoice Belum Dibayar, Revenue Bulan Ini), grafik pendapatan sederhana, list "Recent Activity" |
| Clients | Tabel klien (nama, kontak, jumlah project, status), detail drawer/modal |
| Projects | Kanban board atau tabel (nama project, klien, deadline, status badge) |
| Invoices | Tabel invoice (nomor, klien, jumlah, due date, status Paid/Due/Overdue) |
| Tasks | Checklist per project, filter by status |
| Settings | Form profil, preferensi tema (light/dark), notifikasi |

---

## 10. Tone of Voice UI Copy

- Bahasa singkat, aktif, tidak formal berlebihan: "Tambah Klien" bukan "Silakan tambahkan klien baru Anda".
- Konsisten istilah: pilih salah satu — "Klien" atau "Customer", jangan campur di satu produk.
- Konfirmasi aksi destruktif selalu eksplisit: "Hapus klien ini? Semua data project terkait akan ikut terhapus." bukan sekadar "Yakin?".

---

*Guideline ini adalah living document — update token warna/komponen di sini setiap kali ada perubahan desain agar tetap jadi single source of truth untuk tim (meski solo dev, ini membantu konsistensi jangka panjang).*
