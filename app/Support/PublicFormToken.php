<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class PublicFormToken
{
    public const MIN_DELAY_SECONDS = 3;

    public const MAX_AGE_SECONDS = 1800;

    /**
     * Émet un jeton horodaté signé, à placer dans le formulaire public.
     */
    public static function issue(?CarbonInterface $now = null): string
    {
        $timestamp = (string) ($now ?? Carbon::now())->getTimestamp();

        return $timestamp.'.'.self::signature($timestamp);
    }

    /**
     * Vérifie la signature, le délai minimal et l'expiration du jeton.
     */
    public static function isValid(
        string $token,
        int $minDelaySeconds = self::MIN_DELAY_SECONDS,
        int $maxAgeSeconds = self::MAX_AGE_SECONDS,
        ?CarbonInterface $now = null,
    ): bool {
        if (strlen($token) > 100) {
            return false;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '' || ! ctype_digit($parts[0])) {
            return false;
        }

        [$timestamp, $signature] = $parts;

        if (! hash_equals(self::signature($timestamp), $signature)) {
            return false;
        }

        $elapsedSeconds = ($now ?? Carbon::now())->getTimestamp() - (int) $timestamp;

        return $elapsedSeconds >= $minDelaySeconds
            && $elapsedSeconds <= $maxAgeSeconds;
    }

    private static function signature(string $timestamp): string
    {
        return hash_hmac('sha256', $timestamp, (string) config('app.key'));
    }
}
