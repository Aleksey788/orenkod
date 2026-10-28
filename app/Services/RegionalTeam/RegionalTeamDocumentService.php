<?php

namespace App\Services\RegionalTeam;

use App\Models\RegionalTeam;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class RegionalTeamDocumentService
{
    public function render(RegionalTeam $team, bool $orderDraft = false): string
    {
        $team->load(['ratingSeason', 'discipline', 'ageGroup', 'members' => fn ($query) => $query->whereNull('removed_at')->orderBy('id')]);
        $directory = storage_path('framework/cache/dompdf');
        File::ensureDirectoryExists($directory);
        $pdf = new Dompdf(new Options([
            'defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false, 'isPhpEnabled' => false, 'isJavascriptEnabled' => false,
            'tempDir' => $directory, 'fontCache' => $directory,
            'chroot' => [resource_path('views/regional-teams'), base_path('vendor/dompdf/dompdf/lib/fonts')],
        ]));
        $pdf->loadHtml(view($orderDraft ? 'regional-teams.order-draft' : 'regional-teams.team-list', [
            'team' => $team, 'generatedAt' => now(),
        ])->render(), 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }

    /** @param list<string> $paths */
    public function store(RegionalTeam $team, array &$paths): void
    {
        foreach (['list_path' => false, 'order_path' => true] as $attribute => $orderDraft) {
            $path = $team->id.'/'.Str::uuid().'.pdf';
            $paths[] = $path;
            if (! Storage::disk('team_documents')->put($path, $this->render($team, $orderDraft))) {
                throw new RuntimeException('Could not store team document.');
            }
            $team->$attribute = $path;
        }
    }
}
