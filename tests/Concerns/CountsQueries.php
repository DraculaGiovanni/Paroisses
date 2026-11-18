<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

trait CountsQueries
{
    protected int $queryCount = 0;

    protected array $queries = [];

    protected function startCountingQueries(): void
    {
        $this->queryCount = 0;
        $this->queries = [];

        DB::listen(function ($query) {
            $this->queryCount++;
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];
        });
    }

    protected function getQueryCount(): int
    {
        return $this->queryCount;
    }

    protected function getQueries(): array
    {
        return $this->queries;
    }

    protected function assertQueryCount(int $expected, string $message = ''): void
    {
        $message = $message ?: "Expected {$expected} queries, but {$this->queryCount} were executed.";

        if ($this->queryCount !== $expected) {
            $this->fail($message."\n\nQueries executed:\n".$this->formatQueries());
        }

        $this->assertEquals($expected, $this->queryCount, $message);
    }

    protected function assertQueryCountLessThanOrEqual(int $max, string $message = ''): void
    {
        $message = $message ?: "Expected at most {$max} queries, but {$this->queryCount} were executed.";

        if ($this->queryCount > $max) {
            $this->fail($message."\n\nQueries executed:\n".$this->formatQueries());
        }

        $this->assertLessThanOrEqual($max, $this->queryCount, $message);
    }

    protected function detectNPlusOne(): array
    {
        $patterns = [];
        $nPlusOneDetected = [];

        foreach ($this->queries as $index => $query) {
            $sql = $query['sql'];

            // Normaliser la requête (remplacer les valeurs liées par des placeholders)
            $normalized = preg_replace('/\d+/', '?', $sql);

            if (isset($patterns[$normalized])) {
                $patterns[$normalized]++;

                // Si la même requête apparaît plus de 2 fois, c'est suspect
                if ($patterns[$normalized] > 2) {
                    $nPlusOneDetected[$normalized] = $patterns[$normalized];
                }
            } else {
                $patterns[$normalized] = 1;
            }
        }

        return $nPlusOneDetected;
    }

    protected function assertNoNPlusOne(string $message = ''): void
    {
        $nPlusOne = $this->detectNPlusOne();

        if (! empty($nPlusOne)) {
            $message = $message ?: 'N+1 query problem detected!';
            $details = "\n\nRepeated queries:\n";

            foreach ($nPlusOne as $query => $count) {
                $details .= "- Executed {$count} times: {$query}\n";
            }

            $this->fail($message.$details."\n\nAll queries:\n".$this->formatQueries());
        }

        $this->assertTrue(empty($nPlusOne), $message);
    }

    protected function formatQueries(): string
    {
        $output = '';

        foreach ($this->queries as $index => $query) {
            $output .= ($index + 1).". {$query['sql']}\n";

            if (! empty($query['bindings'])) {
                $output .= '   Bindings: '.json_encode($query['bindings'])."\n";
            }

            $output .= "   Time: {$query['time']}ms\n\n";
        }

        return $output;
    }

    protected function dumpQueries(): void
    {
        echo "\n\n=== SQL Queries ({$this->queryCount} total) ===\n\n";
        echo $this->formatQueries();
    }
}
