<?php

namespace App\Support;

use RuntimeException;

class IntentionCode
{
    /**
     * Alphabet sans caractères ambigus (pas de 0, 1, I, L ni O).
     */
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public const LENGTH = 10;

    /**
     * Génère un code de suivi avec un générateur cryptographique.
     */
    public static function generate(): string
    {
        $alphabetLength = strlen(self::ALPHABET);
        $code = '';

        for ($position = 0; $position < self::LENGTH; $position++) {
            $code .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }

    /**
     * Génère un code unique en réessayant en cas de collision.
     *
     * @param  callable(string): bool  $exists
     */
    public static function generateUnique(callable $exists, int $maxAttempts = 10): string
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = self::generate();

            if (! $exists($code)) {
                return $code;
            }
        }

        throw new RuntimeException('Impossible de générer un code de suivi unique.');
    }

    /**
     * Vérifie qu'un code respecte le format attendu.
     */
    public static function isValid(string $code): bool
    {
        return strlen($code) === self::LENGTH
            && strspn($code, self::ALPHABET) === self::LENGTH;
    }

    /**
     * Normalise un code saisi (majuscules, sans espaces).
     */
    public static function normalize(string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', $code) ?? '');
    }
}
