<?php

namespace Tests\Unit;

use App\Support\ColorContrast;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ColorContrastTest extends TestCase
{
    #[Test]
    public function it_accepts_dark_colors_with_good_contrast()
    {
        // Couleurs qui passent WCAG AA (4.5:1 avec blanc)
        $this->assertTrue(ColorContrast::meetsMinimumContrast('#767676')); // gris foncé - limite ~4.54
        $this->assertTrue(ColorContrast::meetsMinimumContrast('#000000')); // noir - 21:1
        $this->assertTrue(ColorContrast::meetsMinimumContrast('#1E40AF')); // bleu foncé - ~8.5
        $this->assertTrue(ColorContrast::meetsMinimumContrast('#047857')); // vert foncé - ~5.2
    }

    #[Test]
    public function it_rejects_light_colors_with_poor_contrast()
    {
        // Couleurs qui échouent WCAG AA (< 4.5:1)
        $this->assertFalse(ColorContrast::meetsMinimumContrast('#777777')); // gris trop clair - ~4.48
        $this->assertFalse(ColorContrast::meetsMinimumContrast('#FFFFFF')); // blanc - 1:1
        $this->assertFalse(ColorContrast::meetsMinimumContrast('#FFD700')); // or clair - ~1.2
        $this->assertFalse(ColorContrast::meetsMinimumContrast('#FFAAAA')); // rose clair - ~2.1
    }

    #[Test]
    public function it_calculates_correct_luminance_for_pure_colors()
    {
        // Noir = 0, Blanc = 1
        $this->assertEquals(0.0, ColorContrast::calculateRelativeLuminance('#000000'));
        $this->assertEquals(1.0, ColorContrast::calculateRelativeLuminance('#FFFFFF'));

        // Luminance de #767676 (gris limite)
        $luminance = ColorContrast::calculateRelativeLuminance('#767676');
        $this->assertGreaterThan(0.18, $luminance);
        $this->assertLessThan(0.19, $luminance);
    }

    #[Test]
    public function it_handles_lowercase_and_uppercase_hex()
    {
        $this->assertTrue(ColorContrast::meetsMinimumContrast('#1e40af'));
        $this->assertTrue(ColorContrast::meetsMinimumContrast('#1E40AF'));
        $this->assertTrue(ColorContrast::meetsMinimumContrast('#1E40Af'));
    }
}
