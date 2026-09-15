<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves a persona's private media file through a short-lived signed URL.
 */
class PersonaMediaController extends Controller
{
    public function show(Request $request, Persona $persona, string $path): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 404);
        abort_unless($persona->isMediaPath($path), 404);
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->response($path);
    }
}
