<?php
/**
 * This file is part of the AceColors package
 * https://github.com/aVadim483/ace-colors
 *
 * Based on Mexitek/PHPColors by Arlo Carreon <http://arlocarreon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\AceColors;

/**
 * A helpers for converting and manipulating colors. You can use and converting to different formats and finding color harmonies
 *
 * Correct input HEX-strings are
 *      '#RRGGBB'   - full color without alpha, where RR, GG, BB are 2-digits hexadecimal numbers, ex. '#ff9966'
 *      '#RGB'      - short color without alpha, where R, G, B are 1-digit hexadecimal numbers, ex. '#f96'
 *      '#RRGGBBAA' - full color with alpha, ex. '#ff9966cc' is equivalent of rgba(255,153,102,80%) or rgba(255,63,42,0.8)
 *      '#RGBA'     - short color with alpha, the same as above
 * The character '#' can be omitted, so 'ff9966' is equivalent of '#ff9966'
 *
 * Correct input RGB-arrays are
 *      ['r' => 255,    'g' => 0,    'b' => 51]                - range 0 - 255
 *      ['r' => 255,    'g' => 0,    'b' => 51,    'a' => 0.5] - the same, with an explicit alpha value as float
 *      ['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 0.5] - the same color but range 0.0% - 100.0%
 *      ['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 50%] - the same, with an explicit alpha value as percent
 * You can use uppercase indexes, ex. ['R' => 255, 'G' => 0, 'B' => 51]
 *
 * Also RGB-arrays can be with missed indexes:
 *      [255,    0,    51]         - range 0 - 255
 *      [255,    0,    51,    0.5] - the same, with an explicit alpha value as float
 *      ['100%', '0%', '20%', 0.5] - the same color but range 0.0% - 100.0%
 *      ['100%', '0%', '20%', 50%] - the same, with an explicit alpha value as percent
 *
 * Correct input HSL-arrays are
 *      ['h' => 120, 's' => 1, 'l' => 0.5]                     - 'h' is range 0 - 360, 's' and 'l' are range 0.0 - 1.0
 *      ['h' => 120, 's' => 1, 'l' => 0.5, 'a' => 0.3]         - the same, with an explicit alpha value as float
 *      ['h' => 120, 's' => '100%', 'l' => '50%']              - the same, with float values as percents
 *      ['h' => 120, s' => '100%', 'l' => '50%', 'a' => '30%'] - the same
 * You can use uppercase indexes, ex. ['H' => 120, 'S' => 1, 'L' => 0.5]
 *
 * @property $hex
 * @property $red
 * @property $green
 * @property $blue
 * @property $alpha
 * @property $hue
 * @property $saturation
 * @property $lightness
 */
class AceColors
{
    private $_hex;
    private $_hsl;
    private $_rgba;

    /**
     * Auto darkens/lightens by 10% for sexily-subtle gradients.
     * Set this to FALSE to adjust automatic shade to be between given color
     * and black (for darken) or white (for lighten)
     */
    const DEFAULT_ADJUST = 10;

    /**
     * Instantiates the class with a HEX value
     * @param string $hex
     *
     * @throws \RuntimeException
     */
    public function __construct($hex = null)
    {
        if (null === $hex) {
            $color = ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 1.0];
        } else {
            $color = self::_checkHex($hex);
        }
        $this->setRgb($color);
    }

    /**
     * @param $hex
     *
     * @return $this
     */
    public function setHex($hex)
    {
        $color = self::_checkHex($hex);

        return $this->setRgb($color);
    }

    /**
     * @param array $rgb
     *
     * @return $this
     */
    public function setRgb($rgb)
    {
        $color = self::_checkRgb($rgb);

        $this->_hsl = self::rgbToHsl($color);
        $this->_hex = $color;
        $this->_rgba = $color;

        return $this;
    }

    /**
     * @param array $hsl
     *
     * @return $this
     */
    public function setHsl($hsl)
    {
        $hsl = self::_checkHsl($hsl);

        return $this->setRgb(static::hslToRgb($hsl));
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setRed($value)
    {
        $this->red = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setGreen($value)
    {
        $this->green = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setBlue($value)
    {
        $this->blue = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setAlpha($value)
    {
        $this->alpha = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setHue($value)
    {
        $this->hue = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setSaturation($value)
    {
        $this->saturation = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setLightness($value)
    {
        $this->lightness = $value;

        return $this;
    }

    /**
     * Given a HEX string returns a HSL array equivalent
     *
     * @param $color
     *
     * @return array HSL associative array
     *
     * @throws \RuntimeException
     */
    public static function hexToHsl($color)
    {
        // Sanity check
        $rgbColor = self::_checkHex($color);

        return static::rgbToHsl($rgbColor);
    }

    /**
    /**
     * Given a RGB array returns a HSL array equivalent
     *
     * @param $color
     *
     * @return array HSL associative array
     */
    public static function rgbToHsl($color)
    {
        $color = self::_checkRgb($color);

        $HSL = [];

        $R = ($color['r'] / 255);
        $G = ($color['g'] / 255);
        $B = ($color['b'] / 255);

        $varMin = min($R, $G, $B);
        $varMax = max($R, $G, $B);
        $delMax = $varMax - $varMin;

        $L = ($varMax + $varMin) / 2;

        if ($delMax == 0) {
            $H = 0;
            $S = 0;
        } else {
            if ($L < 0.5) {
                $S = $delMax / ($varMax + $varMin);
            } else {
                $S = $delMax / (2 - $varMax - $varMin);
            }

            $delR = ((($varMax - $R) / 6) + ($delMax / 2)) / $delMax;
            $delG = ((($varMax - $G) / 6) + ($delMax / 2)) / $delMax;
            $delB = ((($varMax - $B) / 6) + ($delMax / 2)) / $delMax;

            if ($R == $varMax) {
                $H = $delB - $delG;
            } elseif ($G == $varMax) {
                $H = (1 / 3) + $delR - $delB;
            } elseif ($B == $varMax) {
                $H = (2 / 3) + $delG - $delR;
            }

            if ($H < 0) $H++;
            if ($H > 1) $H--;
        }

        $HSL['h'] = ($H * 360);
        $HSL['s'] = $S;
        $HSL['l'] = $L;

        return $HSL;
    }

    /**
     *  Given an RGB(A) associative array returns the equivalent HEX string
     *
     * @param array $rgb
     * @param bool  $alpha
     *
     * @return string HEX-string
     *
     * @throws \RuntimeException
     */
    public static function rgbToHex($rgb, $alpha = false)
    {
        $color = self::_checkRgb($rgb);

        // Convert to hex. Make sure we get 2 digits for decimals
        $hr = ($color['r'] < 16) ? '0' . dechex($color['r']) : dechex($color['r']);
        $hg = ($color['g'] < 16) ? '0' . dechex($color['g']) : dechex($color['g']);
        $hb = ($color['b'] < 16) ? '0' . dechex($color['b']) : dechex($color['b']);
        if (!$alpha) {
            return $hr . $hg . $hb;
        }
        $a = 255 * $color['a'];
        return $hr . $hg . $hb . (($color['a'] < 16) ? '0' . dechex($a) : dechex($a));
    }


    /**
     *  Given an RGBA associative array returns the equivalent HEX string
     *
     * @param array $rgba
     *
     * @return string HEX string
     *
     * @throws \RuntimeException
     */
    public static function rgbaToHex($rgba)
    {
        return static::rgbToHex($rgba, true);
    }

    /**
     *  Given an RGB(A) associative array and returns the string as rgb() function
     *
     * @param array $rgb
     *
     * @return string As 'rgb(RRR, GGG, BBB)
     */
    public static function rgbToStr($rgb)
    {
        $color = self::_checkRgb($rgb);

        return 'rgb(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ')';
    }

    /**
     *  Given an RGBA associative array and returns the string as rgba() function
     *
     * @param array $rgba
     *
     * @return string HEX string
     *
     * @throws \RuntimeException
     */
    public static function rgbaToStr($rgba)
    {
        $color = self::_checkRgb($rgba);

        return 'rgb(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . str_replace(',', '.', '' . $color['a']) . ')';
    }

    /**
     *  Given a HSL associative array returns the equivalent HEX string
     *
     * @param array $hsl
     *
     * @return string HEX string
     *
     * @throws \RuntimeException
     */
    public static function hslToHex($hsl)
    {
        return static::rgbToHex(static::hslToRGB($hsl));
    }

    /**
     *  Given a HSL associative array returns the equivalent RGBA array
     *
     * @param array $hsl
     *
     * @return array RGBA array
     *
     * @throws \RuntimeException
     */
    public static function hslToRgb($hsl)
    {
        // Make sure it's HSL
        $hsl = self::_checkHsl($hsl);

        list($H, $S, $L) = array($hsl['h'] / 360, $hsl['s'], $hsl['l']);

        if ($S == 0) {
            $r = $L * 255;
            $g = $L * 255;
            $b = $L * 255;
        } else {

            if ($L < 0.5) {
                $var_2 = $L * (1 + $S);
            } else {
                $var_2 = ($L + $S) - ($S * $L);
            }

            $var_1 = 2 * $L - $var_2;

            $r = round(255 * self::_hue2rgb($var_1, $var_2, $H + (1 / 3)));
            $g = round(255 * self::_hue2rgb($var_1, $var_2, $H));
            $b = round(255 * self::_hue2rgb($var_1, $var_2, $H - (1 / 3)));
        }
        return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => 1.0];
    }


    /**
     *  Given a HSL associative array returns the equivalent RGBA array
     *
     * @param array $hsla
     *
     * @return array RGBA array
     *
     * @throws \RuntimeException
     */
    public static function hslaToRgba($hsla)
    {
        // Make sure it's HSL
        $hsla = self::_checkHsl($hsla);
        $rgba = static::hslToRgb($hsla);
        $rgba['a'] = $hsla['a'];

        return $rgba;
    }

    /**
     *  Given a HSL associative array returns the equivalent HEX string
     *
     * @param array $hsla
     *
     * @return string HEX string RRGGBBAA
     *
     * @throws \RuntimeException
     */
    public static function hslaToHex($hsla)
    {
        return static::rgbaToHex(static::hslaToRgba($hsla));
    }

    /**
     * Given a HEX string returns a RGB array equivalent
     *
     * @param string $color
     *
     * @return array RGB associative array
     */
    public static function hexToRgb($color)
    {
        // Sanity check
        return self::_checkHex($color);
    }


    /**
     * Given a HEX value, returns a darker color.
     * If no desired amount provided, then the color halfway between given HEX and black will be returned
     *
     * @param int $amount
     *
     * @return string Darker HEX value
     */
    public function darken($amount = self::DEFAULT_ADJUST)
    {
        // Darken
        $darkerHSL = $this->_darken($this->_hsl, $amount);
        // Return as HEX
        return self::hslToHex($darkerHSL);
    }

    /**
     * Given a HEX value, returns a lighter color.
     * If no desired amount provided, then the color halfway between given HEX and white will be returned.
     *
     * @param int $amount
     *
     * @return string Lighter HEX value
     */
    public function lighten($amount = self::DEFAULT_ADJUST)
    {
        // Lighten
        $lighterHSL = $this->_lighten($this->_hsl, $amount);
        // Return as HEX
        return self::hslToHex($lighterHSL);
    }

    /**
     * Given a HEX value, returns a mixed color. If no desired amount provided, then the color mixed by this ratio
     *
     * @param string $hex2 Secondary HEX value to mix with
     * @param int $amount = -100..0..+100
     *
     * @return string mixed HEX value
     */
    public function mix($hex2, $amount = 0)
    {
        $rgb2 = self::hexToRgb($hex2);
        $mixed = $this->_mix($this->_rgba, $rgb2, $amount);
        // Return as HEX
        return self::rgbToHex($mixed);
    }

    /**
     * Creates an array with two shades that can be used to make a gradient
     *
     * @param int $amount Optional percentage amount you want your contrast color
     *
     * @return array An array with a 'light' and 'dark' index
     */
    public function makeGradient($amount = self::DEFAULT_ADJUST)
    {
        // Decide which color needs to be made
        if ($this->isLight()) {
            $lightColor = $this->_hex;
            $darkColor = $this->darken($amount);
        } else {
            $lightColor = $this->lighten($amount);
            $darkColor = $this->_hex;
        }

        // Return our gradient array
        return ['light' => $lightColor, 'dark' => $darkColor];
    }


    /**
     * Returns whether or not given color is considered "light"
     *
     * @param string|Boolean $color
     *
     * @param int $lighterThan
     *
     * @return boolean
     */
    public function isLight($color = false, $lighterThan = 130)
    {
        // Get our color
        $color = ($color) ? $color : $this->_hex;

        // Calculate straight from rbg
        $r = hexdec($color[0] . $color[1]);
        $g = hexdec($color[2] . $color[3]);
        $b = hexdec($color[4] . $color[5]);

        return (($r * 299 + $g * 587 + $b * 114) / 1000 > $lighterThan);
    }

    /**
     * Returns whether or not a given color is considered "dark"
     *
     * @param string|Boolean $color
     *
     * @param int $darkerThan
     *
     * @return boolean
     */
    public function isDark($color = false, $darkerThan = 130)
    {
        // Get our color
        $color = ($color) ? $color : $this->_hex;

        // Calculate straight from rbg
        $r = hexdec($color[0] . $color[1]);
        $g = hexdec($color[2] . $color[3]);
        $b = hexdec($color[4] . $color[5]);

        return (($r * 299 + $g * 587 + $b * 114) / 1000 <= $darkerThan);
    }

    /**
     * Returns the complimentary color
     *
     * @return string Complementary hex color
     *
     */
    public function complementary()
    {
        // Get our HSL
        $hsl = $this->_hsl;

        // Adjust Hue 180 degrees
        $hsl['h'] += ($hsl['h'] > 180) ? -180 : 180;

        // Return the new value in HEX
        return self::hslToHex($hsl);
    }

    public function invert()
    {
        $rgba = $this->_rgba;
        $r = 255 - $rgba['r'];
        $g = 255 - $rgba['g'];
        $b = 255 - $rgba['b'];

        return self::rgbToHex([$r, $g, $b]);
    }

    /**
     * Returns your color's HSL array
     */
    public function getHsl()
    {
        return $this->_hsl;
    }

    /**
     * Returns your original color
     *
     * @param bool $alpha
     *
     * @return string
     */
    public function getHex($alpha = false)
    {
        if (!$alpha) {
            return $this->_hex;
        }
        return static::rgbToHex($this->_rgba, $alpha);
    }

    /**
     * Returns your original color
     *
     * @param bool $alpha
     *
     * @return string
     */
    public function getHexa()
    {
        return static::rgbaToHex($this->_rgba);
    }

    /**
     * Returns your color's RGBA array
     */
    public function getRgb()
    {
        $rgb = $this->_rgba;
        unset($rgb['a']);

        return $rgb;
    }

    /**
     * Returns your color's RGBA array
     */
    public function getRgba()
    {
        return $this->_rgba;
    }

    /**
     * @return string
     */
    public function getRgbStr()
    {
        return self::rgbToStr($this->getRgb());
    }

    /**
     * @return string
     */
    public function getRgbaStr()
    {
        return self::rgbaToStr($this->getRgba());
    }

    /**
     * Relative luminance
     * The relative brightness of any point in a colorspace, normalized to 0 for darkest black and 1 for lightest white
     * Uses SMPTE C / Rec. 709 coefficients, as recommended in WCAG 2.0.
     *
     * @see https://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef
     *
     * @return float
     */
    public function luma()
    {
        $rgb = $this->getRgb();
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $R = ($r <= 0.03928) ? ($r / 12.92) : (($r + 0.055)/1.055) ^ 2.4;
        $G = ($g <= 0.03928) ? ($g / 12.92) : (($g + 0.055)/1.055) ^ 2.4;
        $B = ($b <= 0.03928) ? ($b / 12.92) : (($b + 0.055)/1.055) ^ 2.4;

        $L = 0.2126 * $R + 0.7152 * $G + 0.0722 * $B;

        return $L;
    }

    /**
     * Returns the cross browser CSS3 gradient
     *
     * @param int $amount Optional: percentage amount to light/darken the gradient
     * @param boolean $vintageBrowsers Optional: include vendor prefixes for browsers that almost died out already
     * @param string $prefix Optional: prefix for every lines
     * @param string $suffix Optional: suffix for every lines
     *
     * @return string CSS3 gradient for chrome, safari, firefox, opera and IE10
     */
    public function getCssGradient($amount = self::DEFAULT_ADJUST, $vintageBrowsers = FALSE, $suffix = '', $prefix = '')
    {

        // Get the recommended gradient
        $g = $this->makeGradient($amount);

        $css = "";
        /* fallback/image non-cover color */
        $css .= "{$prefix}background-color: #" . $this->_hex . ";{$suffix}";

        /* IE Browsers */
        $css .= "{$prefix}filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#" . $g['light'] . "', endColorstr='#" . $g['dark'] . "');{$suffix}";

        /* Safari 4+, Chrome 1-9 */
        if ($vintageBrowsers) {
            $css .= "{$prefix}background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#" . $g['light'] . "), to(#" . $g['dark'] . "));{$suffix}";
        }

        /* Safari 5.1+, Mobile Safari, Chrome 10+ */
        $css .= "{$prefix}background-image: -webkit-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";

        /* Firefox 3.6+ */
        if ($vintageBrowsers) {
            $css .= "{$prefix}background-image: -moz-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
        }

        /* Opera 11.10+ */
        if ($vintageBrowsers) {
            $css .= "{$prefix}background-image: -o-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
        }

        /* Unprefixed version (standards): FF 16+, IE10+, Chrome 26+, Safari 7+, Opera 12.1+ */
        $css .= "{$prefix}background-image: linear-gradient(to bottom, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";

        // Return our CSS
        return $css;
    }

    // ===========================
    // = Private Functions Below =
    // ===========================


    /**
     * Darkens a given HSL array
     *
     * @param array $hsl
     * @param int $amount
     *
     * @return array $hsl
     */
    private function _darken($hsl, $amount = self::DEFAULT_ADJUST)
    {
        // Check if we were provided a number
        if ($amount) {
            $hsl['l'] = ($hsl['l'] * 100) - $amount;
            $hsl['l'] = ($hsl['l'] < 0) ? 0 : $hsl['l'] / 100;
        } else {
            // We need to find out how much to darken
            $hsl['l'] = $hsl['l'] / 2;
        }

        return $hsl;
    }

    /**
     * Lightens a given HSL array
     *
     * @param array $hsl
     * @param int $amount
     *
     * @return array $hsl
     */
    private function _lighten($hsl, $amount = self::DEFAULT_ADJUST)
    {
        // Check if we were provided a number
        if ($amount) {
            $hsl['l'] = ($hsl['l'] * 100) + $amount;
            $hsl['l'] = ($hsl['l'] > 100) ? 1 : $hsl['l'] / 100;
        } else {
            // We need to find out how much to lighten
            $hsl['l'] += (1 - $hsl['l']) / 2;
        }

        return $hsl;
    }

    /**
     * Mix 2 rgb colors and return an rgb color
     *
     * @param array $rgb1
     * @param array $rgb2
     * @param int $amount ranged -100..0..+100
     *
     * @return array $rgb
     */
    private function _mix($rgb1, $rgb2, $amount = 0)
    {

        $r1 = ($amount + 100) / 100;
        $r2 = 2 - $r1;

        $rmix = (($rgb1['r'] * $r1) + ($rgb2['r'] * $r2)) / 2;
        $gmix = (($rgb1['g'] * $r1) + ($rgb2['g'] * $r2)) / 2;
        $bmix = (($rgb1['b'] * $r1) + ($rgb2['b'] * $r2)) / 2;

        return ['r' => $rmix, 'g' => $gmix, 'b' => $bmix];
    }

    /**
     * Given a Hue, returns corresponding RGB value
     *
     * @param int $v1
     * @param int $v2
     * @param int $vH
     *
     * @return int
     */
    private static function _hue2rgb($v1, $v2, $vH)
    {
        if ($vH < 0) {
            ++$vH;
        }
        if ($vH > 1) {
            --$vH;
        }
        if ((6 * $vH) < 1) {
            return ($v1 + ($v2 - $v1) * 6 * $vH);
        }
        if ((2 * $vH) < 1) {
            return $v2;
        }
        if ((3 * $vH) < 2) {
            return ($v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6);
        }
        return $v1;

    }

    /**
     * You need to check if you were given a good hex string
     *
     * @param string|array $hex
     *
     * @return array Colors
     *
     * @throws \RuntimeException
     */
    private static function _checkHex($hex)
    {
        $len = strlen($hex);
        // Strip # sign is present
        if (($len === 4 || $len === 5 || $len === 7 || $len === 9) && $hex[0] === '#') {
            $hex = substr($hex, 1);
            $len--;
        }
        if (strspn($hex, '01234567890abcdef') === $len) {
            if ($len === 3) {
                return ['r' => hexdec($hex[0]), 'g' => hexdec($hex[1]), 'b' => hexdec($hex[2]), 'a' => 1.0];
            }
            if ($len === 4) {
                return ['r' => hexdec($hex[0]), 'g' => hexdec($hex[1]), 'b' => hexdec($hex[2]), 'a' => hexdec($hex[2]) / 255];
            }
            if ($len === 6) {
                return ['r' => hexdec(substr($hex, 0, 2)), 'g' => hexdec(substr($hex, 2, 2)), 'b' => hexdec(substr($hex, 4, 2)), 'a' => 1.0];
            }
            if ($len === 8) {
                return ['r' => hexdec(substr($hex, 0, 2)), 'g' => hexdec(substr($hex, 2, 2)), 'b' => hexdec(substr($hex, 4, 2)), 'a' => hexdec(substr($hex, 6, 2)) / 255];
            }
        }
        return null;
    }

    /**
     * @param $rgb
     *
     * @return array
     */
    private static function _checkRgb($rgb)
    {
        // Make sure it's RGB(A)
        if (isset($rgb[0], $rgb[1], $rgb[2])) {
            $rgb['r'] = $rgb[0];
            $rgb['g'] = $rgb[1];
            $rgb['b'] = $rgb[2];
            if (isset($rgb[3])) {
                $rgb['a'] = $rgb[3];
            }
        }
        if (isset($rgb['R'])) {
            $rgb['r'] = $rgb['R'];
        }
        if (isset($rgb['G'])) {
            $rgb['g'] = $rgb['G'];
        }
        if (isset($rgb['B'])) {
            $rgb['b'] = $rgb['B'];
        }
        if (isset($rgb['A'])) {
            $rgb['a'] = $rgb['A'];
        }
        if (!isset($rgb['r'], $rgb['g'], $rgb['b'])) {
            throw new \RuntimeException('Param was not an RGB array');
        }
        if (!isset($rgb['a'])) {
            $rgb['a'] = 1.0;
        }
        $result = [];
        foreach($rgb as $channel => $value) {
            if (in_array($channel, ['r', 'g', 'b', 'a'], true)) {
                if ($value && is_string($value) && substr($value, -1) === '%') {
                    if ($channel === 'a') {
                        $value = (float)$value / 100;
                    } else {
                        $value = (float)$value / 255;
                    }
                }
                $result[$channel] = $value;
            }
        }
        return $result;
    }

    /**
     * @param array $hsl
     *
     * @return array
     */
    private static function _checkHsl($hsl)
    {
        // Make sure it's HSL
        if (isset($hsl[0], $hsl[1], $hsl[2])) {
            $hsl['h'] = $hsl[0];
            $hsl['s'] = $hsl[1];
            $hsl['l'] = $hsl[2];
        }
        if (isset($hsl['H'])) {
            $hsl['h'] = $hsl['H'];
        }
        if (isset($hsl['S'])) {
            $hsl['s'] = $hsl['S'];
        }
        if (isset($hsl['L'])) {
            $hsl['l'] = $hsl['L'];
        }
        if (isset($hsl['A'])) {
            $hsl['a'] = $hsl['A'];
        }
        if (!isset($rgb['a'])) {
            $rgb['a'] = 1.0;
        }
        if (!isset($hsl['h'], $hsl['s'], $hsl['l'])) {
            throw new \RuntimeException('Param was not an HSL array');
        }
        return $hsl;
    }

    /**
     * @param $value
     * @param $base
     *
     * @return float
     */
    protected static function _checkValue($value, $base)
    {
        if ($value && !is_numeric($value) && substr($value, -1) === '%') {
            return (float)$value / 100 * $base;
        }
        return $value;
    }

    /**
     * Converts object into its string representation
     *
     * @return string Colors
     */
    public function __toString()
    {
        return "#" . $this->getHex();
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'hex':
                return $this->__toString();
            case 'red':
            case 'r':
                return $this->_rgba['r'];
            case 'green':
            case 'g':
                return $this->_rgba['g'];
            case 'blue':
            case 'b':
                return $this->_rgba['b'];
            case 'alpha':
            case 'a':
                return $this->_rgba['a'];
            case 'hue':
            case 'h':
                return $this->_hsl['h'];
            case 'saturation':
            case 's':
                return $this->_hsl['s'];
            case 'lightness':
            case 'l':
                return $this->_hsl['l'];
        }
        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        switch (strtolower($name)) {
            case 'red':
            case 'r':
                $this->_rgba['r'] = self::_checkValue($value, 255);
                $this->_hex = static::rgbToHex($this->_rgba);
                $this->_hsl = static::rgbToHsl($this->_hex);
                break;
            case 'green':
            case 'g':
                $this->_rgba['g'] = self::_checkValue($value, 255);
                $this->_hex = static::rgbToHex($this->_rgba);
                $this->_hsl = static::rgbToHsl($this->_hex);
                break;
            case 'blue':
            case 'b':
                $this->_rgba['b'] = self::_checkValue($value, 255);
                $this->_hex = static::rgbToHex($this->_rgba);
                $this->_hsl = static::rgbToHsl($this->_hex);
                break;
            case 'alpha':
            case 'a':
                $this->_rgba['a'] = self::_checkValue($value, 1);
                break;
            case 'hue':
            case 'h':
                $this->_hsl['h'] = self::_checkValue($value, 360);
                $this->_rgba = static::hslToRgb($this->_hsl);
                $this->_hex = static::rgbToHex($this->_rgba);
                break;
            case 'saturation':
            case 's':
                $this->_hsl['s'] = self::_checkValue($value, 1);
                $this->_rgba = static::hslToRgb($this->_hsl);
                $this->_hex = static::rgbToHex($this->_rgba);
                break;
            case 'lightness':
            case 'light':
            case 'l':
                $this->_hsl['l'] = self::_checkValue($value, 1);
                $this->_rgba = static::hslToRgb($this->_hsl);
                $this->_hex = static::rgbToHex($this->_rgba);
                break;
        }
    }
}

// EOF