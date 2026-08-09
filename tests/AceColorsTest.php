<?php

namespace avadim\AceColors;

use PHPUnit\Framework\TestCase;

class AceColorsTest extends TestCase
{
    /**
     * The key case is global for the process, so it must never leak between tests
     */
    protected function tearDown(): void
    {
        AceColors::setKeyCase(CASE_LOWER);

        parent::tearDown();
    }

    /**
     * Test constructor with various inputs
     */
    public function testConstructor()
    {
        // Default (black)
        $color = new AceColors();
        $this->assertEquals('000000', $color->getHex());

        // Hex 6 digits
        $color = new AceColors('ff0000');
        $this->assertEquals('ff0000', $color->getHex());

        // Hex 3 digits
        $color = new AceColors('0f0');
        $this->assertEquals('00ff00', $color->getHex());

        // Hex with #
        $color = new AceColors('#0000ff');
        $this->assertEquals('#0000ff', $color->getHex());

        // Hex with # and useSharp(false)
        $color = new AceColors('#0000ff');
        $color->useSharp(false);
        $this->assertEquals('0000ff', $color->getHex());

        // Force useSharp(true)
        $color = new AceColors('ff0000');
        $color->useSharp(true);
        $this->assertEquals('#ff0000', $color->getHex());

        // Hex with alpha 8 digits
        $color = new AceColors('#ff000080');
        $this->assertEquals('#ff0000', $color->getHex());
        $this->assertEquals(0.5, round($color->getRgba()['a'], 2));

        // Hex with alpha 4 digits
        $color = new AceColors('#f008');
        $this->assertEquals('#ff0000', $color->getHex());
        $this->assertEquals(0.53, round($color->getRgba()['a'], 2));

        // RGB string
        $color = new AceColors('rgb(255, 0, 0)');
        $this->assertEquals('ff0000', $color->getHex());

        // RGBA string
        $color = new AceColors('rgba(0, 255, 0, 0.5)');
        $this->assertEquals('00ff00', $color->getHex());
        $this->assertEquals(0.5, $color->getRgba()['a']);

        // RGB array
        $color = new AceColors(['r' => 0, 'g' => 0, 'b' => 255]);
        $this->assertEquals('0000ff', $color->getHex());

        // RGB array numeric keys
        $color = new AceColors([0, 0, 255]);
        $this->assertEquals('0000ff', $color->getHex());

        // HSL array
        $color = new AceColors(['h' => 0, 's' => 1, 'l' => 0.5]);
        $this->assertEquals('ff0000', $color->getHex());
    }

    /**
     * Test setters
     */
    public function testSetters()
    {
        $color = new AceColors();
        
        $color->setRed(255);
        $this->assertEquals('ff0000', $color->getHex());
        
        $color->setGreen(255);
        $this->assertEquals('ffff00', $color->getHex());
        
        $color->setBlue(0); // Red + Green = Yellow
        $this->assertEquals('ffff00', $color->getHex());
        
        $color->setAlpha(0.5);
        $this->assertEquals(0.5, round($color->getRgba()['a'], 2));
        
        $color->setHue(120); // Green
        $this->assertEquals('00ff00', $color->getHex());
        
        $color->setSaturation(0.5);
        $hsl = $color->getHsl();
        $this->assertEquals(0.5, $hsl['s']);
        
        $color->setLightness(0.5); 
        $this->assertEquals(0.5, $color->getHsl()['l']);
        $this->assertEquals('40bf40', $color->getHex());
    }

    /**
     * Test getters
     */
    public function testGetters()
    {
        $color = new AceColors('ff000080');
        
        $this->assertEquals('ff0000', $color->getHex());
        $this->assertEquals('ff000080', $color->getHexa());
        
        $rgb = $color->getRgb();
        $this->assertEquals(255, $rgb['r']);
        $this->assertEquals(0, $rgb['g']);
        $this->assertEquals(0, $rgb['b']);
        
        $rgba = $color->getRgba();
        $this->assertEquals(0.5, round($rgba['a'] ?? 0, 2));
        
        $this->assertEquals('rgb(255,0,0)', $color->getRgbStr());
        $this->assertStringStartsWith('rgba(255,0,0,0.5', $color->getRgbaStr());
        
        $hsl = $color->getHsl();
        $this->assertEquals(0, $hsl['h']);
        $this->assertEquals(1, $hsl['s']);
        $this->assertEquals(0.5, $hsl['l']);
        
        $hsla = $color->getHslA();
        $this->assertEquals(0.5, round($hsla['a'], 2));
        
        $this->assertEquals('hsl(0,100%,50%)', $color->getHslStr());
        $this->assertStringStartsWith('hsla(0,100%,50%,0.5', $color->getHslaStr());
    }

    /**
     * Test static conversion methods
     */
    public function testStaticConversions()
    {
        $res = AceColors::hexToRgb('#ff0000');
        $this->assertEquals(255, $res['r']);
        $this->assertEquals(0, $res['g']);
        $this->assertEquals(0, $res['b']);
        
        $this->assertEquals('ff0000', AceColors::rgbToHex(['r' => 255, 'g' => 0, 'b' => 0], false));
        $this->assertEquals('ff0000ff', AceColors::rgbaToHexa(['r' => 255, 'g' => 0, 'b' => 0, 'a' => 1.0]));
        $this->assertEquals('rgb(255,0,0)', AceColors::rgbToStr(['r' => 255, 'g' => 0, 'b' => 0]));
        
        $hsl = AceColors::rgbToHsl(['r' => 255, 'g' => 0, 'b' => 0]);
        $this->assertEquals(0, $hsl['h']);
        $this->assertEquals(1, $hsl['s']);
        $this->assertEquals(0.5, $hsl['l']);
        
        $rgb = AceColors::hslToRgb(['h' => 0, 's' => 1, 'l' => 0.5]);
        $this->assertEquals(255, $rgb['r']);
    }

    /**
     * Test color manipulations
     */
    public function testManipulations()
    {
        $color = new AceColors('808080'); // gray
        
        $lighter = $color->cloneColor()->lighten(10);
        $this->assertGreaterThan(0.5, $lighter->getHsl()['l']);
        
        $darker = $color->cloneColor()->darken(10);
        $this->assertLessThan(0.5, $darker->getHsl()['l']);
        
        $color = new AceColors('ff0000');
        $inverted = $color->cloneColor()->invert();
        $this->assertEquals('00ffff', $inverted->getHex());
        
        $compl = $color->cloneColor()->complementary();
        $this->assertEquals(180, $compl->getHsl()['h']);
    }

    /**
     * Test saturate/desaturate
     */
    public function testSaturation()
    {
        // Percents, percent strings and fractions mean the same amount
        foreach ([50, '50%', 0.5] as $amount) {
            $color = (new AceColors('ff0000'))->desaturate($amount);
            $this->assertEquals(0.5, round($color->getHsl()['s'], 4), 'amount: ' . $amount);
            $this->assertEquals('bf4040', $color->getHex());
        }

        // Default amount is DEFAULT_ADJUST (10%)
        $this->assertEquals(0.9, round((new AceColors('ff0000'))->desaturate()->getHsl()['s'], 4));
        // bf4040 is ~0.498 saturated (rgb values are rounded to integers), +10% gives ~0.6
        $this->assertEquals(0.6, round((new AceColors('bf4040'))->saturate()->getHsl()['s'], 2));

        // A negative amount reverses the direction
        $this->assertGreaterThan(0.5, (new AceColors('bf4040'))->desaturate(-30)->getHsl()['s']);
        $this->assertLessThan(0.5, (new AceColors('bf4040'))->saturate(-30)->getHsl()['s']);

        // Result is clamped to 0.0 - 1.0
        $this->assertEquals(0.0, (new AceColors('ff0000'))->desaturate(100)->getHsl()['s']);
        $this->assertEquals(1.0, (new AceColors('bf4040'))->saturate(999)->getHsl()['s']);

        // Full desaturation gives a gray but keeps the hue
        $gray = (new AceColors(['h' => 210, 's' => 0.8, 'l' => 0.5]))->desaturate(100);
        $this->assertEquals('7f7f7f', $gray->getHex());
        $this->assertEquals(210, $gray->getHsl()['h']);

        // Alpha survives
        $this->assertEquals(0.5, round((new AceColors('ff000080'))->desaturate(50)->getRgba()['a'], 2));

        // Mutates in place and is fluent, like lighten()/darken()
        $color = new AceColors('ff0000');
        $this->assertSame($color, $color->desaturate(50));
        $this->assertEquals('bf4040', $color->getHex());
        $this->assertEquals('ff0000', $color->saturate(50)->getHex());
    }

    /**
     * Test luma and light/dark checks
     */
    public function testLumaAndChecks()
    {
        $white = new AceColors('ffffff');
        $black = new AceColors('000000');
        
        $this->assertEquals(1.0, $white->luma());
        $this->assertEquals(0.0, $black->luma());
        
        $this->assertTrue($white->isLight('ffffff', 128));
        $this->assertTrue($black->isDark('000000', 128));
    }

    /**
     * Test gradient methods
     */
    public function testGradients()
    {
        // A dark base color: the light end is lightened, the dark end is the color itself
        $color = new AceColors('ff0000');
        $gradient = $color->getGradientArray(10);

        $this->assertSame(['light' => 'ff3333', 'dark' => 'ff0000'], $gradient);
        $this->assertEquals('ff0000', $color->getHex(), 'getGradientArray() must not modify the color');

        // A light base color: the roles are swapped
        $light = new AceColors('ffcccc');
        $this->assertSame(['light' => 'ffcccc', 'dark' => 'ff9999'], $light->getGradientArray(10));
        $this->assertEquals('ffcccc', $light->getHex(), 'getGradientArray() must not modify the color');

        // Both ends are bare HEX strings of the same type, not a string plus an object
        foreach ([new AceColors('ff0000'), new AceColors('ffcccc')] as $c) {
            $g = $c->getGradientArray(10);
            $this->assertIsString($g['light']);
            $this->assertIsString($g['dark']);
            $this->assertNotEquals($g['light'], $g['dark'], 'gradient ends must differ');
        }

        // CSS is valid: every color is prefixed with exactly one '#'
        $css = (new AceColors('ff0000'))->getCssGradient(10);
        $this->assertStringContainsString('linear-gradient(to bottom, #ff3333, #ff0000)', $css);
        $this->assertStringContainsString('background-color: #ff0000;', $css);
        $this->assertStringNotContainsString('##', $css);

        // Vendor-prefixed branch
        $vintage = (new AceColors('ff0000'))->getCssGradient(10, true);
        $this->assertStringContainsString('-moz-linear-gradient', $vintage);
        $this->assertStringContainsString('-o-linear-gradient', $vintage);
        $this->assertStringNotContainsString('##', $vintage);
    }
    /**
     * Test the non-mutating make*() family
     */
    public function testMakeMethods()
    {
        $base = '3388cc';

        $expected = [
            'makeDarker'        => '296da3',
            'makeLighter'       => '5ca0d6',
            'makeInverted'      => 'cc7733',
            'makeComplimentary' => 'cc7733',
        ];

        foreach ($expected as $method => $hex) {
            $color = new AceColors($base);
            $made = $color->$method();

            $this->assertNotSame($color, $made, $method . '() must return a new object');
            $this->assertEquals($hex, $made->getHex(), $method . '() result');
            $this->assertEquals($base, $color->getHex(), $method . '() must not modify the original');
        }

        // cloneColor() is independent of the source
        $color = new AceColors($base);
        $clone = $color->cloneColor();
        $clone->setRed(0);
        $this->assertEquals($base, $color->getHex());
        $this->assertEquals('0088cc', $clone->getHex());

        // Explicit amount is passed through
        $this->assertEquals('1f527a', (new AceColors($base))->makeDarker(20)->getHex());
    }

    /**
     * Test mix()
     */
    public function testMix()
    {
        // amount 0 is an equal mix, +100 keeps the current color, -100 gives the other one
        $this->assertEquals('7f007f', (new AceColors('ff0000'))->mix('0000ff')->getHex());
        $this->assertEquals('bf003f', (new AceColors('ff0000'))->mix('0000ff', 50)->getHex());
        $this->assertEquals('3f00bf', (new AceColors('ff0000'))->mix('0000ff', -50)->getHex());
        $this->assertEquals('ff0000', (new AceColors('ff0000'))->mix('0000ff', 100)->getHex());
        $this->assertEquals('0000ff', (new AceColors('ff0000'))->mix('0000ff', -100)->getHex());

        // An AceColors instance is accepted as well as a string
        $this->assertEquals('7f007f', (new AceColors('ff0000'))->mix(new AceColors('0000ff'))->getHex());

        // mix() mutates in place and is fluent
        $color = new AceColors('ff0000');
        $this->assertSame($color, $color->mix('0000ff'));
        $this->assertEquals('7f007f', $color->getHex());

        // Alpha of the current color survives
        $this->assertEquals(0.5, round((new AceColors('ff000080'))->mix('0000ff')->getRgba()['a'], 2));
    }

    /**
     * Test setHex() and setRgbStr()
     */
    public function testSetHexAndRgbStr()
    {
        $color = new AceColors();
        $this->assertSame($color, $color->setHex('#00ff00'));
        // setHex() does not turn the '#' prefix on, it only comes from the constructor or useSharp()
        $this->assertEquals('00ff00', $color->getHex());

        // ...and an already enabled prefix is kept
        $this->assertEquals('#00ff00', (new AceColors('#000'))->setHex('00ff00')->getHex());

        // Short notation and alpha
        $this->assertEquals('00ff00', (new AceColors())->setHex('0f0')->getHex());
        $this->assertEquals(0.5, round((new AceColors())->setHex('00ff0080')->getRgba()['a'], 2));

        $color = new AceColors();
        $this->assertSame($color, $color->setRgbStr('rgb(0,0,255)'));
        $this->assertEquals('0000ff', $color->getHex());
        $this->assertEquals(0.5, (new AceColors())->setRgbStr('rgba(0,0,255,0.5)')->getRgba()['a']);
    }

    /**
     * Test static conversions that go through HSL
     */
    public function testStaticHslConversions()
    {
        // hsl(210, 50%, 40%) converts to exactly rgb(51, 102, 153) with no fractional
        // channels. Values landing on x.5 must be avoided here: PHP below 8.4 pre-rounded
        // inside round(), so 127.49999999999994 became 128, while 8.4 returns 127
        $hsl = ['h' => 210, 's' => 0.5, 'l' => 0.4];
        $hsla = $hsl + ['a' => 0.5];

        $this->assertEquals('336699', AceColors::hslToHex($hsl));
        $this->assertEquals('336699', AceColors::hslaToHex($hsla), 'hslaToHex() drops the alpha');
        $this->assertEquals('3366997f', AceColors::hslaToHexa($hsla));
        $this->assertEquals('3388cc', AceColors::rgbaToHex(['r' => 51, 'g' => 136, 'b' => 204, 'a' => 0.5]),
            'rgbaToHex() drops the alpha, rgbaToHexa() keeps it');

        $this->assertEquals('hsl(210,50%,40%)', AceColors::hslToStr($hsl));
        $this->assertEquals('hsla(210,50%,40%,0.5)', AceColors::hslaToStr($hsla));
        $this->assertEquals('rgba(51,136,204,0.5)', AceColors::rgbaToStr(['r' => 51, 'g' => 136, 'b' => 204, 'a' => 0.5]));

        // hexToHsl() round-trips back to the same color
        $back = AceColors::hexToHsl('#336699');
        $this->assertEquals(210, round($back['h']));
        $this->assertEquals(0.5, round($back['s'], 2));
        $this->assertEquals(0.4, round($back['l'], 2));
        $this->assertEquals('336699', AceColors::hslToHex($back));

        // hslToRgba() keeps the alpha, hslToRgb() strips the key entirely
        $this->assertEquals(0.5, AceColors::hslToRgba($hsla)['a']);
        $this->assertArrayNotHasKey('a', AceColors::hslToRgb($hsla));
    }

    /**
     * Test the documented input formats
     */
    public function testInputFormats()
    {
        // Percent values
        $this->assertEquals('ff0033', (new AceColors(['r' => '100%', 'g' => '0%', 'b' => '20%']))->getHex());
        $this->assertEquals(0.5, (new AceColors(['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 0.5]))->getRgba()['a']);

        // Uppercase keys, including the alpha one
        $this->assertEquals('ff0033', (new AceColors(['R' => 255, 'G' => 0, 'B' => 51]))->getHex());
        $this->assertEquals('00ff00', (new AceColors(['H' => 120, 'S' => 1, 'L' => 0.5]))->getHex());
        $this->assertEquals(0.5, (new AceColors(['R' => 255, 'G' => 0, 'B' => 51, 'A' => 0.5]))->getRgba()['a']);
        $this->assertEquals(0.5, (new AceColors(['H' => 120, 'S' => 1, 'L' => 0.5, 'A' => 0.5]))->getRgba()['a']);

        // Hue above 240 degrees exercises the wrap-around inside the HSL to RGB conversion
        $this->assertEquals('ff00ff', (new AceColors(['h' => 300, 's' => 1, 'l' => 0.5]))->getHex());
        $this->assertEquals('ff0080', (new AceColors(['h' => 330, 's' => 1, 'l' => 0.5]))->getHex());

        // Positional arrays, with and without alpha
        $this->assertEquals('ff0033', (new AceColors([255, 0, 51]))->getHex());
        $this->assertEquals(0.5, (new AceColors([255, 0, 51, 0.5]))->getRgba()['a']);
        $this->assertEquals('00ff00', (new AceColors(['h' => 120, 's' => 1, 'l' => 0.5, 'a' => 0.3]))->getHex());

        // hsl()/hsla() strings
        $this->assertEquals('00ff00', (new AceColors('hsl(120,100%,50%)'))->getHex());
        $color = new AceColors('hsla(120,100%,50%,0.5)');
        $this->assertEquals('00ff00', $color->getHex());
        $this->assertEquals(0.5, $color->getRgba()['a']);

        // Percent alpha in an rgba() string
        $color = new AceColors('rgba(255,0,51,80%)');
        $this->assertEquals('ff0033', $color->getHex());
        $this->assertEquals(0.8, round($color->getRgba()['a'], 2));

        // '#' alone means black with the prefix enabled
        $this->assertEquals('#000000', (new AceColors('#'))->getHex());
    }

    /**
     * Every method of the 4.2.1 list switches its keys, and nothing else does
     */
    public function testKeyCaseOutput()
    {
        $rgb = ['r' => 51, 'g' => 136, 'b' => 204];
        $hsl = ['h' => 210, 's' => 0.5, 'l' => 0.4];
        $hsla = $hsl + ['a' => 0.5];

        $collect = static function () use ($rgb, $hsl, $hsla) {
            $color = new AceColors('3388cc');

            return [
                'getRgb'     => array_keys($color->getRgb()),
                'getRgba'    => array_keys($color->getRgba()),
                'getHsl'     => array_keys($color->getHsl()),
                'getHslA'    => array_keys($color->getHslA()),
                'hexToRgb'   => array_keys(AceColors::hexToRgb('#3388cc')),
                'hexToRgba'  => array_keys(AceColors::hexToRgba('#3388cc')),
                'hexToHsl'   => array_keys(AceColors::hexToHsl('#3388cc')),
                'rgbToHsl'   => array_keys(AceColors::rgbToHsl($rgb)),
                'hslToRgb'   => array_keys(AceColors::hslToRgb($hsl)),
                'hslToRgba'  => array_keys(AceColors::hslToRgba($hsl)),
                'hslaToRgba' => array_keys(AceColors::hslaToRgba($hsla)),
            ];
        };

        // Default is lowercase
        $this->assertEquals(CASE_LOWER, AceColors::getKeyCase());
        foreach ($collect() as $method => $keys) {
            $this->assertEquals($keys, array_map('strtolower', $keys), $method . '() must return lowercase keys by default');
        }

        AceColors::setKeyCase(CASE_UPPER);
        $this->assertEquals(CASE_UPPER, AceColors::getKeyCase());
        foreach ($collect() as $method => $keys) {
            $this->assertEquals($keys, array_map('strtoupper', $keys), $method . '() must return uppercase keys');
        }

        // Concrete shape, not just the case
        $this->assertSame(['R', 'G', 'B'], array_keys(AceColors::hexToRgb('#3388cc')));
        $this->assertSame(['R', 'G', 'B', 'A'], array_keys(AceColors::hexToRgba('#3388cc')));
        $this->assertSame(['H', 'S', 'L'], array_keys(AceColors::hexToHsl('#3388cc')));

        // Restoring the default works
        AceColors::setKeyCase(CASE_LOWER);
        $this->assertSame(['r', 'g', 'b'], array_keys(AceColors::hexToRgb('#3388cc')));

        // An unsupported value falls back to the default rather than corrupting the state
        AceColors::setKeyCase(12345);
        $this->assertEquals(CASE_LOWER, AceColors::getKeyCase());
    }

    /**
     * Regression on the 4.2.2 requirement: the flag must not leak into the internals.
     * Every operation has to produce exactly the same color in both modes
     */
    public function testKeyCaseDoesNotAffectBehaviour()
    {
        $probe = static function () {
            $magicRed = new AceColors('3388cc');
            $magicRed->red = 0;
            $magicHue = new AceColors('3388cc');
            $magicHue->h = 120;
            $magicLight = new AceColors('3388cc');
            $magicLight->light = 0.2;

            return [
                'luma'       => round((new AceColors('#3388cc'))->luma(), 6),
                'mix'        => (new AceColors('ff0000'))->mix('0000ff')->getHex(),
                'lighten'    => (new AceColors('808080'))->lighten(10)->getHex(),
                'darken'     => (new AceColors('808080'))->darken(10)->getHex(),
                'saturate'   => (new AceColors('bf4040'))->saturate(20)->getHex(),
                'desaturate' => (new AceColors('ff0000'))->desaturate(50)->getHex(),
                'complement' => (new AceColors('ff0000'))->complementary()->getHex(),
                'invert'     => (new AceColors('ff0000'))->invert()->getHex(),
                'setRed'     => (new AceColors('000000'))->setRed(255)->getHex(),
                'setAlpha'   => (new AceColors('ff0000'))->setAlpha('80%')->getHexa(),
                'setHue'     => (new AceColors('3388cc'))->setHue(120)->getHex(),
                'setSat'     => (new AceColors('3388cc'))->setSaturation(50)->getHex(),
                'setLight'   => (new AceColors('3388cc'))->setLightness(20)->getHex(),
                'magicRed'   => $magicRed->getHex(),
                'magicHue'   => $magicHue->getHex(),
                'magicLight' => $magicLight->getHex(),
                'setHsl'     => (new AceColors())->setHsl(['h' => 120, 's' => 1, 'l' => 0.5])->getHex(),
                'setRgb'     => (new AceColors())->setRgb(['r' => 255, 'g' => 0, 'b' => 51])->getHex(),
                'setHex'     => (new AceColors())->setHex('#00ff00')->getHex(),
                'hexa'       => (new AceColors('3388cc80'))->getHexa(),
                'hslToHex'   => AceColors::hslToHex(['h' => 210, 's' => 0.5, 'l' => 0.4]),
                'hslaToHexa' => AceColors::hslaToHexa(['h' => 210, 's' => 0.5, 'l' => 0.4, 'a' => 0.5]),
                'rgbToHex'   => AceColors::rgbToHex(['r' => 51, 'g' => 136, 'b' => 204]),
                'isLight'    => (new AceColors('ffffff'))->isLight(),
            ];
        };

        $lower = $probe();
        AceColors::setKeyCase(CASE_UPPER);
        $upper = $probe();

        $this->assertSame($lower, $upper, 'the key case must not change any computed color');
    }

    /**
     * String methods ignore the key case entirely
     */
    public function testKeyCaseDoesNotAffectStrings()
    {
        $strings = static function () {
            return [
                (new AceColors('3388cc'))->getRgbStr(),
                (new AceColors('3388cc80'))->getRgbaStr(),
                (new AceColors('3388cc'))->getHslStr(),
                (new AceColors('3388cc80'))->getHslaStr(),
                (new AceColors('#3388cc'))->getHex(),
                AceColors::hslToStr(['h' => 210, 's' => 0.5, 'l' => 0.4]),
                AceColors::hslaToStr(['h' => 210, 's' => 0.5, 'l' => 0.4, 'a' => 0.5]),
                AceColors::rgbToStr(['r' => 51, 'g' => 136, 'b' => 204]),
                AceColors::rgbaToStr(['r' => 51, 'g' => 136, 'b' => 204, 'a' => 0.5]),
            ];
        };

        $lower = $strings();
        AceColors::setKeyCase(CASE_UPPER);

        $this->assertSame($lower, $strings());
    }

    /**
     * getGradientArray() keys are roles, not color channels, so they stay as they are
     */
    public function testKeyCaseSkipsGradientArray()
    {
        AceColors::setKeyCase(CASE_UPPER);

        $gradient = (new AceColors('ff0000'))->getGradientArray(10);
        $this->assertSame(['light' => 'ff3333', 'dark' => 'ff0000'], $gradient);

        // ...and the CSS built on top of it stays valid
        $css = (new AceColors('ff0000'))->getCssGradient(10);
        $this->assertStringContainsString('linear-gradient(to bottom, #ff3333, #ff0000)', $css);
        $this->assertStringNotContainsString('##', $css);
    }

    /**
     * Uppercase input keeps working whatever the output case is
     */
    public function testKeyCaseInputIsAlwaysAccepted()
    {
        foreach ([CASE_LOWER, CASE_UPPER] as $case) {
            AceColors::setKeyCase($case);

            $this->assertEquals('hsl(120,50%,50%)', AceColors::hslToStr(['H' => 120, 'S' => 0.5, 'L' => 0.5]));
            $this->assertEquals('hsl(120,50%,50%)', AceColors::hslToStr(['h' => 120, 's' => 0.5, 'l' => 0.5]));
            $this->assertEquals('ff0033', AceColors::rgbToHex(['R' => 255, 'G' => 0, 'B' => 51]));
            $this->assertEquals('ff0033', (new AceColors(['R' => 255, 'G' => 0, 'B' => 51]))->getHex());
            $this->assertEquals('00ff00', (new AceColors(['H' => 120, 'S' => 1, 'L' => 0.5]))->getHex());
        }
    }

    /**
     * Alpha symmetry for the hex converters
     */
    public function testHexAlphaSymmetry()
    {
        // hexToRgb() drops the alpha key entirely
        // hexdec() yields integers here, unlike _checkRgb() which casts to float
        $this->assertSame(['r' => 255, 'g' => 0, 'b' => 0], AceColors::hexToRgb('#ff0000'));
        $this->assertArrayNotHasKey('a', AceColors::hexToRgb('#ff000080'));

        // hexToRgba() keeps it, and it is null when the input has no alpha
        $rgba = AceColors::hexToRgba('#ff0000');
        $this->assertArrayHasKey('a', $rgba);
        $this->assertNull($rgba['a']);

        $rgba = AceColors::hexToRgba('#ff000080');
        $this->assertEquals(0.5, round($rgba['a'], 2));

        // Short notation works in both
        // hexdec() yields integers here, unlike _checkRgb() which casts to float
        $this->assertSame(['r' => 255, 'g' => 0, 'b' => 0], AceColors::hexToRgb('f00'));
        $this->assertEquals(0.53, round(AceColors::hexToRgba('#f008')['a'], 2));

        // Both still throw on garbage rather than returning null
        $this->expectException(\RuntimeException::class);
        AceColors::hexToRgba('zzzzzz');
    }

    /**
     * Test the '#' prefix handling
     */
    public function testUseSharp()
    {
        $color = new AceColors('ff0000');
        $this->assertEquals('ff0000', $color->getHex());
        $this->assertSame($color, $color->useSharp(true));
        $this->assertEquals('#ff0000', $color->getHex());
        $this->assertEquals('#ff0000ff', $color->getHexa());
        $this->assertEquals('ff0000', $color->useSharp(false)->getHex());

        // __toString() always yields exactly one '#', whatever the prefix setting is
        $this->assertEquals('#ff0000', (string)(new AceColors('ff0000')));
        $this->assertEquals('#ff0000', (string)(new AceColors('#ff0000')));
        $this->assertEquals('#ff0000', (string)(new AceColors('ff0000'))->useSharp(true));
    }

    /**
     * Test that every documented error code is reachable
     *
     * @dataProvider invalidInputProvider
     */
    public function testInvalidInput(callable $call, string $expectedMessage)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $call();
    }

    public function invalidInputProvider(): array
    {
        return [
            'garbage string' => [static function () { new AceColors('invalid color'); }, 'Wrong color format'],
            'empty array' => [static function () { new AceColors([]); }, 'Wrong color format'],
            'unknown array keys' => [static function () { new AceColors(['x' => 1, 'y' => 2, 'z' => 3]); }, 'Wrong color format'],
            'bad hex' => [static function () { AceColors::hexToRgb('zzzzzz'); }, 'Wrong format of HEX string'],
            'bad rgb array' => [static function () { AceColors::rgbToHex(['x' => 1]); }, 'Wrong format of RGB array'],
            'bad hsl array' => [static function () { AceColors::hslToRgb(['x' => 1]); }, 'Wrong format of HSL array'],
            'hue as percent' => [static function () { AceColors::hslToRgb(['h' => '50%', 's' => 1, 'l' => 0.5]); }, 'Wrong format of HSL array'],
            'unknown property read' => [static function () { (new AceColors('f00'))->nosuch; }, 'Unknown color property'],
            'unknown property write' => [static function () { $c = new AceColors('f00'); $c->nosuch = 1; }, 'Unknown color property'],
        ];
    }

    /**
     * Test magic methods
     */
    public function testMagicMethods()
    {
        $color = new AceColors('ff0000');
        
        // __get
        $this->assertEquals(255, $color->red);
        $this->assertEquals(0, $color->green);
        $this->assertEquals(0, $color->blue);
        
        // __set
        $color->red = 0;
        $color->green = 255;
        $this->assertEquals('00ff00', $color->getHex());
        
        // __toString
        $this->assertEquals('#' . $color->getHex(), (string)$color);
    }

    /**
     * Test all __get/__set aliases
     */
    public function testMagicAliases()
    {
        $color = new AceColors('#3388cc');

        $this->assertEquals('#3388cc', $color->hex);
        $this->assertEquals([51, 136, 204], [$color->r, $color->g, $color->b]);
        $this->assertEquals([51, 136, 204], [$color->red, $color->green, $color->blue]);
        $this->assertEquals(207, round($color->h));
        $this->assertEquals(0.6, round($color->s, 2));
        $this->assertEquals(0.5, round($color->l, 2));
        $this->assertEquals(round($color->hue), round($color->h));
        $this->assertEquals($color->saturation, $color->s);
        $this->assertEquals($color->lightness, $color->l);

        // An unset alpha reads as null, not as 1.0
        $this->assertNull($color->a);
        $this->assertNull($color->alpha);

        $this->assertEquals($color->luma(), $color->luma);
        $this->assertEquals($color->luma(), $color->luminance);

        // Short __set aliases go through the same paths as the setters
        $color = new AceColors('3388cc');
        $color->a = 0.5;
        $this->assertEquals(0.5, $color->alpha);

        $color = new AceColors('3388cc');
        $color->h = 120;
        $this->assertEquals('33cc33', $color->getHex());

        $color = new AceColors('3388cc');
        $color->s = 0.2;
        $this->assertEquals('668299', $color->getHex());

        // 'light' is a third alias for lightness
        $color = new AceColors('3388cc');
        $color->light = 0.2;
        $this->assertEquals('143652', $color->getHex());
    }

    /**
     * Test the "halfway" branch of lighten()/darken(), used when the amount is 0
     */
    public function testLightenDarkenHalfway()
    {
        // Halfway to white
        $lighter = (new AceColors('808080'))->lighten(0);
        $this->assertEquals(0.75, round($lighter->getHsl()['l'], 2));
        $this->assertEquals('bfbfbf', $lighter->getHex());

        // Halfway to black
        $darker = (new AceColors('808080'))->darken(0);
        $this->assertEquals(0.25, round($darker->getHsl()['l'], 2));
        $this->assertEquals('404040', $darker->getHex());
    }

    /**
     * Test percent and '#' notation accepted by the value setters
     */
    public function testValueFormats()
    {
        // Percent of the channel range, and hex notation
        $this->assertEquals(127.5, (new AceColors('000'))->setRed('50%')->red);
        $this->assertEquals(255, (new AceColors('000'))->setRed('#ff')->red);

        // Alpha accepts percent strings, 0-100 numbers and plain fractions alike
        foreach (['80%', 80, 0.8] as $value) {
            $this->assertEquals(0.8, round((new AceColors('f00'))->setAlpha($value)->getRgba()['a'], 4),
                'setAlpha(' . var_export($value, true) . ')');
        }

        // Numbers above 1 are treated as percents here as well
        $color = (new AceColors('3388cc'))->setSaturation(50);
        $this->assertEquals(0.5, round($color->getHsl()['s'], 4));
        $this->assertEquals('4087bf', $color->getHex());

        $color = (new AceColors('3388cc'))->setLightness(20);
        $this->assertEquals(0.2, round($color->getHsl()['l'], 4));
        $this->assertEquals('143652', $color->getHex());

        // Fractional channel values survive the conversion to hex without an
        // implicit float-to-int deprecation on PHP 8.1+
        $this->assertEquals('7f0000', (new AceColors('000'))->setRed('50%')->getHex());
        $this->assertEquals('ff00007f', AceColors::rgbaToHexa(['r' => 255, 'g' => 0, 'b' => 0, 'a' => 0.5]));

        // isLight()/isDark() accept a color argument with or without '#'
        $this->assertTrue((new AceColors('000'))->isLight('#ffffff'));
        $this->assertTrue((new AceColors('fff'))->isDark('#000000'));
    }

    /**
     * A positional array is ambiguous: the constructor always reads it as RGB,
     * only setHsl() reads it as HSL
     */
    public function testPositionalHslArray()
    {
        $this->assertEquals('00ff00', (new AceColors())->setHsl([120, 1, 0.5])->getHex());

        $color = (new AceColors())->setHsl([120, 1, 0.5, 0.5]);
        $this->assertEquals('00ff00', $color->getHex());
        $this->assertEquals(0.5, $color->getRgba()['a']);

        // The very same array in the constructor means rgb(120, 1, 0.5)
        $this->assertEquals('780100', (new AceColors([120, 1, 0.5]))->getHex());
    }

    /**
     * Test exceptions
     */
    public function testExceptions()
    {
        $this->expectException(\RuntimeException::class);
        new AceColors('invalid color');
    }

    /**
     * Internal calls go through static::, so a subclass can still override the math.
     * Guards the self:: / static:: convention: overridable members are called late-bound
     */
    public function testLateStaticBinding()
    {
        $green = ['h' => 120, 's' => 1, 'l' => 0.5];

        $this->assertEquals('00ff00', AceColors::hslToHex($green));
        $this->assertEquals('ff0000', SubColors::hslToHex($green),
            'hslToHex() must call the converter late-bound, otherwise subclasses cannot extend it');

        // ...the same for the instance path
        $this->assertEquals('ff0000', (new SubColors())->setHsl($green)->getHex());
    }
}

/**
 * Fixture for testLateStaticBinding(): replaces the HSL math with a constant red
 */
class SubColors extends AceColors
{
    protected static function _hslToRgb(array $hsl): array
    {
        return ['r' => 255, 'g' => 0, 'b' => 0];
    }

    protected static function _hslToRgba(array $hsl): array
    {
        return ['r' => 255, 'g' => 0, 'b' => 0, 'a' => $hsl['a'] ?? null];
    }
}
