<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Client;
use App\Models\Project;
use App\Models\VaultFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VaultController extends Controller
{
    use ValidatesCrmRelations;

    // TODO(audit V6): file upload/download cannot be tested statically (no vendor,
    //   and PHP upload limits are environment-specific). Verify on a running app:
    //   upload_max_filesize / post_max_size in php.ini, and that download is gated
    //   by the `pin` middleware. See AUDIT-TODO.md.

    private const DISK = 'local';

    private const DIR = 'vault';

    public function index(Request $request): View
    {
        $files = VaultFile::query()
            ->with(['client', 'project'])
            ->search($request->string('search')->toString())
            ->when($request->filled('folder'), fn ($query) => $query->where('folder', $request->string('folder')->toString()))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('vault.index', [
            'files' => $files,
            'folders' => VaultFile::query()->whereNotNull('folder')->distinct()->orderBy('folder')->pluck('folder'),
            'clients' => Client::orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['required', 'file', 'max:25600'], // 25 MB per file
            'folder' => ['nullable', 'string', 'max:100'],
            'client_id' => ['nullable', $this->ownedExists('clients')],
            'project_id' => ['nullable', $this->ownedExists('projects')],
        ]);

        foreach ($request->file('files') as $upload) {
            $path = $upload->store(self::DIR, self::DISK);
            if (! $path) {
                throw ValidationException::withMessages(['files' => 'File belum tersimpan. Coba lagi.']);
            }

            VaultFile::create([
                'name' => $upload->getClientOriginalName(),
                'original_name' => $upload->getClientOriginalName(),
                'mime' => Storage::disk(self::DISK)->mimeType($path) ?: 'application/octet-stream',
                'size' => $upload->getSize(),
                'disk' => self::DISK,
                'path' => $path,
                'folder' => $validated['folder'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
            ]);
        }

        return redirect()->route('vault.index')->with('status', 'File berhasil diupload.');
    }

    public function preview(VaultFile $file): StreamedResponse
    {
        $this->authorizeOwner($file);

        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);

        // Inspect stored bytes, including old uploads whose metadata came from the client.
        $mime = Storage::disk($file->disk)->mimeType($file->path) ?: 'application/octet-stream';
        if (! in_array($mime, [...VaultFile::PREVIEW_IMAGE_TYPES, 'application/pdf'], true)) {
            return Storage::disk($file->disk)->download($file->path, $file->original_name, [
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return response()->stream(function () use ($file) {
            $stream = Storage::disk($file->disk)->readStream($file->path);
            if ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Content-Disposition' => 'inline; filename="'.addslashes($file->original_name).'"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function download(VaultFile $file): StreamedResponse
    {
        $this->authorizeOwner($file);

        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function destroy(VaultFile $file): RedirectResponse
    {
        $this->authorizeOwner($file);
        $file->delete(); // model's deleting hook removes the physical file

        return redirect()->route('vault.index')->with('status', 'File dihapus.');
    }
}
