<?php

namespace App\Support;

class ColorContrast
{
    /**
     * Vérifie qu'une couleur hexadécimale a un contraste suffisant avec le blanc.
     *
     * Formule WCAG 2.1 :
     * - Linéariser chaque canal sRGB : c <= 0.03928 ? c/12.92 : ((c+0.055)/1.055)^2.4
     * - Luminance relative : 0.2126*R + 0.7152*G + 0.0722*B
     * - Contraste : (L1 + 0.05) / (L2 + 0.05), L1 = max(L_blanc, L_couleur)
     *
     * @param  string  $hexColor  Format #RRGGBB (validé en amont par regex)
     * @param  float  $minRatio  Ratio minimum (4.5 pour WCAG AA, 7.0 pour AAA)
     */
    public static function meetsMinimumContrast(string $hexColor, float $minRatio = 4.5): bool
    {
        $luminance = self::calculateRelativeLuminance($hexColor);
        $contrastRatio = self::calculateContrastRatio(1.0, $luminance); // blanc = 1.0

        return $contrastRatio >= $minRatio;
    }

    /**
     * Calcule la luminance relative d'une couleur hexadécimale.
     *
     * @param  string  $hexColor  Format #RRGGBB
     * @return float Valeur entre 0 (noir) et 1 (blanc)
     */
    public static function calculateRelativeLuminance(string $hexColor): float
    {
        $hex = ltrim($hexColor, '#');

        // Conversion hex → RGB (0-255) → sRGB (0-1)
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Linéarisation de chaque canal selon WCAG
        $rLinear = self::linearize($r);
        $gLinear = self::linearize($g);
        $bLinear = self::linearize($b);

        // Luminance relative avec pondération perceptuelle
        return 0.2126 * $rLinear + 0.7152 * $gLinear + 0.0722 * $bLinear;
    }

    /**
     * Linéarise un canal sRGB selon la formule WCAG.
     *
     * @param  float  $channel  Valeur sRGB entre 0 et 1
     * @return float Valeur linéaire entre 0 et 1
     */
    private static function linearize(float $channel): float
    {
        if ($channel <= 0.03928) {
            return $channel / 12.92;
        }

        return pow(($channel + 0.055) / 1.055, 2.4);
    }

    /**
     * Calcule le ratio de contraste entre deux luminances.
     *
     * @param  float  $l1  Luminance de la couleur la plus claire
     * @param  float  $l2  Luminance de la couleur la plus sombre
     * @return float Ratio de contraste (1:1 à 21:1)
     */
    private static function calculateContrastRatio(float $l1, float $l2): float
    {
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }
}
