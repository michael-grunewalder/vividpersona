<?php

namespace App\Services\Media;

use App\Exceptions\StorageLimitExceededException;
use App\Models\Persona;
use App\Models\Team;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Central gateway for storing persona media on the private disk. Tracks per-team
 * usage and enforces the team's storage quota.
 */
final class PersonaMediaService
{
    private const DOWNLOAD_TIMEOUT = 60;

    /**
     * Download a remote image into the persona's private media folder and return
     * the stored relative path. Always persists locally (used for generated
     * assets outside the wizard's pick flow).
     */
    public function storeFromUrl(Persona $persona, string $url, string $prefix = ''): string
    {
        $response = Http::timeout(self::DOWNLOAD_TIMEOUT)->get($url);

        if ($response->failed()) {
            throw new RuntimeException("Failed to download media from [{$url}].");
        }

        $extension = match (true) {
            str_contains((string) $response->header('Content-Type'), 'png') => 'png',
            str_contains((string) $response->header('Content-Type'), 'webp') => 'webp',
            default => 'jpeg',
        };

        $path = $this->newPath($persona, $prefix, $extension);

        $this->put($persona->team, $path, $response->body());

        return $path;
    }

    /**
     * Store an uploaded file into the persona's private media folder.
     */
    public function storeUploaded(Persona $persona, UploadedFile $file, string $prefix): string
    {
        $path = $this->newPath($persona, $prefix, $file->extension() ?: 'bin');

        $this->put($persona->team, $path, (string) file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Delete a stored file and decrement the team's usage.
     */
    public function delete(Team $team, string $path): void
    {
        $disk = Storage::disk('private');

        if (! $disk->exists($path)) {
            return;
        }

        $size = $disk->size($path);
        $disk->delete($path);
        $team->decrement('storage_used_bytes', max(0, $size));
    }

    /**
     * Throw when storing the given number of bytes would exceed the quota.
     */
    public function assertWithinLimit(Team $team, int $bytes): void
    {
        if (! $team->canStoreBytes($bytes)) {
            throw new StorageLimitExceededException($team);
        }
    }

    /**
     * Recompute the team's used bytes from the actual files on disk.
     */
    public function reconcile(Team $team): int
    {
        $disk = Storage::disk('private');
        $total = 0;

        foreach ($disk->allFiles((string) $team->getKey()) as $file) {
            $total += $disk->size($file);
        }

        $team->update(['storage_used_bytes' => $total]);

        return $total;
    }

    private function newPath(Persona $persona, string $prefix, string $extension): string
    {
        $name = (string) Str::ulid();

        return $persona->mediaFolder().'/'.($prefix !== '' ? $prefix.'-' : '').$name.'.'.$extension;
    }

    private function put(Team $team, string $path, string $contents): void
    {
        $this->assertWithinLimit($team, strlen($contents));

        Storage::disk('private')->put($path, $contents);
        $team->increment('storage_used_bytes', strlen($contents));
    }
}
