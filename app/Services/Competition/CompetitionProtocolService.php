<?php

namespace App\Services\Competition;

use App\Models\AuditLog;
use App\Models\Competition;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CompetitionProtocolService
{
    public function __construct(private CompetitionResultService $results) {}

    public function render(Competition $competition): string
    {
        $competition->loadMissing(['discipline', 'competitionType', 'ageGroup', 'judgeAssignments.judge.user:id,first_name,last_name,middle_name']);
        $rows = $this->results->standings($competition);
        $directory = storage_path('framework/cache/dompdf');
        File::ensureDirectoryExists($directory);

        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'tempDir' => $directory,
            'fontCache' => $directory,
            'chroot' => [resource_path('views/competitions'), base_path('vendor/dompdf/dompdf/lib/fonts')],
        ]);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('competitions.protocol', [
            'competition' => $competition,
            'rows' => $rows,
            'generatedAt' => now(),
            'counts' => $rows->countBy(fn (array $row): string => $row['result_status'] ?? 'missing'),
        ])->render(), 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return $pdf->output();
    }

    public function preview(Competition $competition, int $userId): string
    {
        return DB::transaction(function () use ($competition, $userId): string {
            $competition = Competition::query()->whereKey($competition->id)->lockForUpdate()->firstOrFail();
            $contents = $competition->results_published_at !== null
                ? Storage::disk('protocols')->get($competition->protocol_path)
                : $this->render($competition);
            $this->audit($competition, $userId, 'protocol.previewed');

            return $contents;
        });
    }

    public function publish(Competition $competition, int $userId): Competition
    {
        $path = null;

        try {
            return DB::transaction(function () use ($competition, $userId, &$path): Competition {
                $competition = Competition::query()->whereKey($competition->id)->lockForUpdate()->firstOrFail();
                $this->results->ensureCompleted($competition);

                if ($competition->results_published_at !== null) {
                    if (! $competition->protocol_path || ! Storage::disk('protocols')->exists($competition->protocol_path)) {
                        throw new RuntimeException('Published protocol is missing.');
                    }

                    return $competition;
                }

                $this->results->ensureComplete($competition);
                $competition->forceFill([
                    'results_published_at' => now(),
                    'results_published_by_user_id' => $userId,
                ]);
                $contents = $this->render($competition);
                $path = $competition->id.'/'.Str::uuid().'.pdf';
                if (! Storage::disk('protocols')->put($path, $contents)) {
                    throw new RuntimeException('Could not write protocol.');
                }

                $competition->forceFill([
                    'protocol_path' => $path,
                ])->save();
                $this->audit($competition, $userId, 'protocol.generated');
                $this->audit($competition, $userId, 'results.published');

                return $competition;
            });
        } catch (Throwable $exception) {
            if ($path !== null) {
                Storage::disk('protocols')->delete($path);
            }

            throw $exception;
        }
    }

    private function audit(Competition $competition, int $userId, string $event): void
    {
        AuditLog::create([
            'user_id' => $userId,
            'active_role' => 'admin',
            'event' => $event,
            'subject_type' => $competition->getMorphClass(),
            'subject_id' => $competition->id,
            'new_values' => [
                'results_published_at' => $competition->results_published_at?->toIso8601String(),
                'results_published_by_user_id' => $competition->results_published_by_user_id,
            ],
        ]);
    }
}
