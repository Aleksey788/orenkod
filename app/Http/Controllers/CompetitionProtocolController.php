<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Services\Competition\CompetitionProtocolService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompetitionProtocolController extends Controller
{
    public function __construct(private CompetitionProtocolService $protocols) {}

    public function preview(Request $request, Competition $competition): Response|StreamedResponse
    {
        if ($competition->results_published_at !== null) {
            return $this->download($competition);
        }

        return response($this->protocols->preview($competition, $request->user()->id), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="draft-protocol-'.$competition->id.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Competition $competition): StreamedResponse
    {
        abort_unless($competition->status === 'published' && $competition->results_published_at !== null, 404);
        abort_unless($competition->protocol_path && Storage::disk('protocols')->exists($competition->protocol_path), 404);

        return Storage::disk('protocols')->download($competition->protocol_path, 'protocol-'.$competition->id.'.pdf', [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
