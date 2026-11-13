<?php

namespace App\Services\Migration;

class MigrationReport
{
    /** @var array<string, array{source:int, imported:int, skipped:int, failed:int}> */
    private array $counts = [];

    /** @var array<string, list<string>> */
    private array $notes = [];

    public function source(string $type, int $n = 1): void
    {
        $this->bump($type, 'source', $n);
    }

    public function imported(string $type, int $n = 1): void
    {
        $this->bump($type, 'imported', $n);
    }

    public function skipped(string $type, string $why): void
    {
        $this->bump($type, 'skipped', 1);
        $this->note('Skipped', "$type: $why");
    }

    public function failed(string $type, string $why): void
    {
        $this->bump($type, 'failed', 1);
        $this->note('Failed', "$type: $why");
    }

    public function note(string $group, string $message): void
    {
        $this->notes[$group][] = $message;
    }

    /** @return array<string, array{source:int, imported:int, skipped:int, failed:int}> */
    public function counts(): array
    {
        return $this->counts;
    }

    /** @return array<string, list<string>> */
    public function notes(): array
    {
        return $this->notes;
    }

    public function toMarkdown(bool $dryRun): string
    {
        $md = '# WordPress to Laravel migration report'."\n\n";
        $md .= 'Generated '.now()->toDateTimeString().($dryRun ? ' (DRY RUN: nothing saved)' : '')."\n\n";
        $md .= "| Type | Source | Imported | Skipped | Failed |\n|---|---|---|---|---|\n";
        foreach ($this->counts as $type => $c) {
            $md .= "| $type | {$c['source']} | {$c['imported']} | {$c['skipped']} | {$c['failed']} |\n";
        }
        foreach ($this->notes as $group => $items) {
            $unique = array_count_values($items);
            $md .= "\n## $group (".count($items).")\n\n";
            foreach ($unique as $msg => $n) {
                $md .= "- $msg".($n > 1 ? " (x$n)" : '')."\n";
            }
        }

        return $md;
    }

    private function bump(string $type, string $key, int $n): void
    {
        $this->counts[$type] ??= ['source' => 0, 'imported' => 0, 'skipped' => 0, 'failed' => 0];
        $this->counts[$type][$key] += $n;
    }
}
