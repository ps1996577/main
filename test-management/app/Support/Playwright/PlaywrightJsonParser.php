<?php

namespace App\Support\Playwright;

class PlaywrightJsonParser
{
    /**
     * @param array<string,mixed> $report
     * @return array{
     *   total:int, passed:int, failed:int, skipped:int, flaky:int,
     *   status:string,
     *   startedAt:?string, finishedAt:?string
     * }
     */
    public static function summarize(array $report): array
    {
        $totals = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'flaky' => 0,
        ];

        foreach (self::flattenTests($report) as $t) {
            $totals['total']++;
            $totals[$t['status']] = ($totals[$t['status']] ?? 0) + 1;
        }

        $status = 'unknown';
        if ($totals['total'] > 0) {
            $status = $totals['failed'] > 0 ? 'failed' : 'passed';
        }

        $startedAt = null;
        $finishedAt = null;

        $stats = $report['stats'] ?? null;
        if (is_array($stats)) {
            if (isset($stats['startTime']) && is_string($stats['startTime'])) {
                $startedAt = $stats['startTime'];
            }
            if (isset($stats['duration']) && is_numeric($stats['duration']) && $startedAt) {
                $seconds = (int) ceil(((float) $stats['duration']) / 1000);
                $finishedAt = date(DATE_ATOM, strtotime($startedAt) + $seconds);
            }
        }

        return $totals + [
            'status' => $status,
            'startedAt' => $startedAt,
            'finishedAt' => $finishedAt,
        ];
    }

    /**
     * @param array<string,mixed> $report
     * @return array<int,array{title:string,location:?string,status:string,project:?string,durationMs:int}>
     */
    public static function flattenTests(array $report): array
    {
        $tests = [];

        $suites = $report['suites'] ?? [];
        if (! is_array($suites)) {
            return [];
        }

        foreach ($suites as $suite) {
            self::walkSuite($suite, '', $tests);
        }

        return $tests;
    }

    /**
     * @param mixed $suite
     * @param string $prefix
     * @param array<int,array{title:string,location:?string,status:string,project:?string,durationMs:int}> $out
     */
    private static function walkSuite(mixed $suite, string $prefix, array &$out): void
    {
        if (! is_array($suite)) {
            return;
        }

        $title = isset($suite['title']) && is_string($suite['title']) ? $suite['title'] : '';
        $currentPrefix = trim($prefix . ' ' . $title);

        $tests = $suite['tests'] ?? [];
        if (is_array($tests)) {
            foreach ($tests as $test) {
                if (! is_array($test)) {
                    continue;
                }

                $testTitle = isset($test['title']) && is_string($test['title']) ? $test['title'] : '(unnamed test)';
                $fullTitle = $currentPrefix !== '' ? ($currentPrefix . ' › ' . $testTitle) : $testTitle;

                $project = isset($test['projectName']) && is_string($test['projectName']) ? $test['projectName'] : null;

                $durationMs = 0;
                $resultStatuses = [];
                $results = $test['results'] ?? [];
                if (is_array($results)) {
                    foreach ($results as $r) {
                        if (! is_array($r)) {
                            continue;
                        }
                        if (isset($r['duration']) && is_numeric($r['duration'])) {
                            $durationMs += (int) $r['duration'];
                        }
                        if (isset($r['status']) && is_string($r['status'])) {
                            $resultStatuses[] = $r['status'];
                        }
                    }
                }

                $status = self::normalizeStatus($resultStatuses);
                $location = null;
                if (isset($test['location']) && is_array($test['location'])) {
                    $file = isset($test['location']['file']) && is_string($test['location']['file']) ? $test['location']['file'] : null;
                    $line = isset($test['location']['line']) && is_numeric($test['location']['line']) ? (int) $test['location']['line'] : null;
                    if ($file) {
                        $location = $line ? "{$file}:{$line}" : $file;
                    }
                }

                $out[] = [
                    'title' => $fullTitle,
                    'location' => $location,
                    'status' => $status,
                    'project' => $project,
                    'durationMs' => $durationMs,
                ];
            }
        }

        $childSuites = $suite['suites'] ?? [];
        if (is_array($childSuites)) {
            foreach ($childSuites as $child) {
                self::walkSuite($child, $currentPrefix, $out);
            }
        }
    }

    /**
     * @param array<int,string> $statuses
     */
    private static function normalizeStatus(array $statuses): string
    {
        $statuses = array_values(array_filter(array_map('strval', $statuses)));
        $unique = array_values(array_unique($statuses));

        if ($unique === []) {
            return 'skipped';
        }

        $hasPassed = in_array('passed', $unique, true);
        $hasFailed = in_array('failed', $unique, true) || in_array('timedOut', $unique, true) || in_array('interrupted', $unique, true);
        $hasSkipped = in_array('skipped', $unique, true);

        if ($hasPassed && $hasFailed) {
            return 'flaky';
        }
        if ($hasFailed) {
            return 'failed';
        }
        if ($hasSkipped && ! $hasPassed) {
            return 'skipped';
        }

        return 'passed';
    }
}

