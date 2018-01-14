<?php
/**
 * This file is part of the PhpColor package
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
    const ERROR_COLOR_FORMAT    = 1000;
    const ERROR_HEX_FORMAT      = 1010;
    const ERROR_HEXA_FORMAT     = 1011;
    const ERROR_RGB_FORMAT      = 1020;
    const ERROR_RGBA_FORMAT     = 1021;
    const ERROR_HSL_FORMAT      = 1030;
    const ERROR_HSLA_FORMAT     = 1031;
    const ERROR_RGB_STR_FORMAT  = 1040;
    const ERROR_RGBA_STR_FORMAT = 1041;
    const ERROR_NO_PROPERTY     = 2001;

    private $_hex;
    private $_hsl;
    private $_rgb;
    private $_alpha;

    protected static $errors = [];

    /**
     * Auto darkens/lightens by 10% for sexily-subtle gradients.
     * Set this to FALSE to adjust automatic shade to be between given color
     * and black (for darken) or white (for lighten)
     */
    const DEFAULT_ADJUST = 10;

    /**
     * AceColors constructor
     *
     * @param string|array $input
     */
    public function __construct($input = null)
    {
        self::_setErrors();
        $color = null;
        if (null === $input) {
            $color = ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 1.0];
            $this->setRgb($color);
        } elseif (is_string($input)) {
            if ($color = self::_checkHex($input, true)) {
                $this->setRgb($color);
            } elseif ($color = self::_checkRgbStr($input, true)) {
                $this->setRgb($color);
            } elseif ($color = self::_checkHslStr($input, true)) {
                $this->setHsl($color);
            }
        } elseif (is_array($input)) {
            if ($color = self::_checkRgb($input, true)) {
                $this->setRgb($color);
            } elseif ($color = self::_checkHsl($input, true)) {
                $this->setHsl($color);
            } else {
                static::_error(self::ERROR_COLOR_FORMAT);
            }
        }
        if (empty($color)) {
            static::_error(self::ERROR_COLOR_FORMAT);
        }
    }

    /**
     * Set error codes and messages
     */
    protected static function _setErrors()
    {
        self::$errors = [
            self::ERROR_COLOR_FORMAT    => 'Wrong color format',
            self::ERROR_HEX_FORMAT      => 'Wrong format of HEX string (required "#RRGGBB" or "#RGB")',
            self::ERROR_HEXA_FORMAT     => 'Wrong format of HEXA string (required "#RRGGBBAA" or "#RGBA")',
            self::ERROR_RGB_FORMAT      => 'Wrong format of RGB array (required ["r"=>R,"g"=>G,"b"=>B]',
            self::ERROR_RGBA_FORMAT     => 'Wrong format of RGBA array (required ["r"=>R,"g"=>G,"b"=>B,"a"=>A]',
            self::ERROR_HSL_FORMAT      => 'Wrong format of HSL array (required ["h"=>H,"s"=>S,"l"=>L]',
            self::ERROR_HSLA_FORMAT     => 'Wrong format of HSLA array (required ["h"=>H,"s"=>S,"l"=>L,"a"=>A]',
            self::ERROR_RGB_STR_FORMAT  => 'Wrong format of RGB_STR string (required "rgb(R,G,B)")',
            self::ERROR_RGBA_STR_FORMAT => 'Wrong format of RGBA_STR string (required "rgba(R,G,B,A)")',
        ];
    }

    /**
     * @param $code
     */
    protected static function _error($code)
    {
        if (!empty(self::$errors[$code])) {
            throw new \RuntimeException(self::$errors[$code]);
        }
        throw new \RuntimeException('Unknown error');
    }


    /* ***************************************
     * Set colors
     */

    /**
     * Given a HEX(A) string and set color
     *
     * @param string $hex String in HEX or HEXA (hex with alpha) format
     *
     * @return $this
     */
    public function setHex($hex)
    {
        $color = self::_checkHex($hex);

        return $this->setRgb($color);
    }

    /**
     * Given a RGB(A) array and set color
     *
     * @param array $rgb Array in RGB or RGBA format
     *
     * @return $this
     */
    public function setRgb($rgb)
    {
        $rgbaColor = self::_checkRgb($rgb);
        $this->_setRgbaColor($rgbaColor);

        return $this;
    }

    /**
     * Given a 'rgb(...)' or 'rgba(...)' string and set color
     *
     * @param string $rgbStr
     *
     * @return $this
     */
    public function setRgbStr($rgbStr)
    {
        $rgba = self::_checkRgbStr($rgbStr, true);
        if ($rgba) {
            $this->setRgb($rgba);
        }
        return $this;
    }

    /**
     * Given a HSL(A) array and set color
     *
     * @param array $hsl Array in HSL or HSLA (with alpha) format
     *
     * @return $this
     */
    public function setHsl($hsl)
    {
        $hsl = self::_checkHsl($hsl);
        if ($hsl) {
            if (isset($hsl['a'])) {
                $this->_setRgbaColor(static::hslaToRgba($hsl), $hsl);
            } else {
                $this->_setRgbaColor(static::hslToRgb($hsl), $hsl);
            }
        }
        return $this;
    }


    /* ***************************************
     * Set color components
     */

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


    /* *****************************************************
     * Methods return the current color in different formats
     */

    /**
     * Returns current color as HEX-string
     *
     * @return string
     */
    public function getHex()
    {
        return $this->_hex;
    }

    /**
     * Returns current color as HEXA-string
     *
     * @return string
     */
    public function getHexa()
    {
        $hexa = $this->getHex();
        $hexa .= (null === $this->_alpha) ? 'ff' : dechex($this->_alpha * 255);

        return $hexa;
    }

    /**
     * Returns current color as RGB-array
     *
     * @return array
     */
    public function getRgb()
    {
        return $this->_rgb;
    }

    /**
     * Returns current color as RGBA-array
     *
     * @return array
     */
    public function getRgba()
    {
        $rgba = $this->getRgb();
        $rgba['a'] = (null === $this->_alpha) ? 1.0 : $this->_alpha;

        return $rgba;
    }

    /**
     * Returns current color as string 'rgb(...)'
     *
     * @return string
     */
    public function getRgbStr()
    {
        return self::rgbToStr($this->getRgb());
    }

    /**
     * Returns current color as string 'rgba(...)'
     *
     * @return string
     */
    public function getRgbaStr()
    {
        return self::rgbaToStr($this->getRgba());
    }

    /**
     * Returns current color as HSL array
     *
     * @return array
     */
    public function getHsl()
    {
        return $this->_hsl;
    }

    /**
     * Returns current color as HSLA array
     *
     * @return array
     */
    public function getHsla()
    {
        $hsla = $this->getHsl();
        $hsla['a'] = (null === $this->_alpha) ? 1.0 : $this->_alpha;

        return $hsla;
    }

    /**
     * Returns current color as string 'hsl(...)'
     *
     * @return string
     */
    public function getHslStr()
    {
        return static::hslToStr($this->getHsl());
    }

    /**
     * Returns current color as string 'hsla(...)'
     *
     * @return string
     */
    public function getHslaStr()
    {
        return static::hslaToStr($this->getHsla());
    }


    /* ***************************************
     * Convert colors form one format to other
     */

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
     * Given a HEX string returns a HSL array equivalent
     *
     * @param $color
     *
     * @return array HSL associative array
     */
    public static function hexToHsl($color)
    {
        // Sanity check
        $rgbColor = self::_checkHex($color);

        return static::rgbToHsl($rgbColor);
    }

    /**
     *  Given an RGB(A) associative array returns the equivalent HEX string
     *
     * @param array $rgb
     * @param bool  $alpha
     *
     * @return string HEX(A)-string
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
        return $hr . $hg . $hb . (($a < 16) ? '0' . dechex($a) : dechex($a));
    }

    /**
     *  Given an RGBA associative array returns the equivalent HEXA string
     *
     * @param array $rgba
     *
     * @return string HEXA string
     */
    public static function rgbaToHex($rgba)
    {
        return static::rgbToHex($rgba, false);
    }

    /**
     *  Given an RGBA associative array returns the equivalent HEXA string
     *
     * @param array $rgba
     *
     * @return string HEXA string
     */
    public static function rgbaToHexa($rgba)
    {
        return static::rgbToHex($rgba, true);
    }

    /**
     *  Given an RGB(A) associative array and returns the string as rgb() function
     *
     * @param array $rgb
     *
     * @return string As 'rgb(R, G, B)
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
     * @return string As 'rgba(R, G, B, A)
     */
    public static function rgbaToStr($rgba)
    {
        $color = self::_checkRgb($rgba);

        return 'rgb(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ',' . str_replace(',', '.', '' . $color['a']) . ')';
    }

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

            if ($H < 0) {
                $H++;
            }
            if ($H > 1) {
                $H--;
            }
        }

        $HSL['h'] = ($H * 360);
        $HSL['s'] = $S;
        $HSL['l'] = $L;

        return $HSL;
    }

    /**
     *  Given a HSL associative array returns the equivalent HEX string
     *
     * @param array $hsl
     *
     * @return string HEX string
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
     * @return array RGB-array
     */
    public static function hslToRgb($hsl)
    {
        $rgb = static::hslToRgba($hsl);
        unset($rgb['a']);

        return $rgb;
    }

    /**
     *  Given a HSL associative array returns the equivalent RGBA array
     *
     * @param array $hsl
     *
     * @return array RGBA array
     */
    public static function hslToRgba($hsl)
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
        return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => isset($hsl['a']) ? $hsl['a'] : null];
    }


    /**
     *  Given a HSL associative array returns the equivalent RGBA array
     *
     * @param array $hsla
     *
     * @return array RGBA array
     */
    public static function hslaToRgba($hsla)
    {
        return static::hslToRgba($hsla);
    }

    /**
     *  Given a HSL associative array returns the equivalent HEX string
     *
     * @param array $hsla
     *
     * @return string HEX string RRGGBB
     */
    public static function hslaToHex($hsla)
    {
        return static::rgbToHex(static::hslToRgb($hsla));
    }

    /**
     *  Given a HSL associative array returns the equivalent HEX string
     *
     * @param array $hsla
     *
     * @return string HEX string RRGGBBAA
     */
    public static function hslaToHexa($hsla)
    {
        return static::rgbaToHexa(static::hslaToRgba($hsla));
    }

    /**
     *  Given an HSL associative array and returns the string as 'hsl(...)' function
     *
     * @param array $hsl
     *
     * @return string As 'hsl(R, G, B)
     */
    public static function hslToStr($hsl)
    {
        $color = self::_checkHsl($hsl);

        return 'hsl(' . $color['h'] . ',' . ($color['s'] * 100) . '%,' . ($color['l'] * 100) . '%)';
    }

    /**
     *  Given an HSLA associative array and returns the string as 'hsl(...)' function
     *
     * @param array $hsl
     *
     * @return string As 'hsla(H, S, L, A)
     */
    public static function hslaToStr($hsl)
    {
        $color = self::_checkHsl($hsl);

        return 'hsla(' . $color['h'] . ',' . ($color['s'] * 100) . '%,' . ($color['l'] * 100) . ',%' . str_replace(',', '.', '' . $color['a']) . ')';
    }


    /* ***************************************
     * Make new color objects
     */

    /**
     * Make new color object with the same color
     *
     * @return $this
     */
    public function cloneColor()
    {
        $color = clone $this;

        return $color;
    }

    /**
     * Make new color object with inverted color
     *
     * @return $this
     */
    public function makeInverted()
    {
        return $this->cloneColor()->invert();
    }

    /**
     * Make new color object with complementary color
     *
     * @return string
     */
    public function makeComplimentary()
    {
        $color = $this->cloneColor();

        return $this->cloneColor()->complementary();
    }

    /**
     * Make new color object with darker color
     *
     * @return string
     */
    public function makeDarker($amount = self::DEFAULT_ADJUST)
    {
        return $this->cloneColor()->darken($amount);
    }

    /**
     * @param int $amount
     *
     * @return string
     */
    public function makeLighter($amount = self::DEFAULT_ADJUST)
    {
        return $this->cloneColor()->lighten($amount);
    }

    /* ***************************************
     * Other methods
     */

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
     * Returns whether or not given color is considered "light"
     *
     * @param string|bool $color
     *
     * @param int $lighterThan
     *
     * @return bool
     */
    public function isLight($color = false, $lighterThan = 130)
    {
        // Get current color
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
     * @param string|bool $color
     *
     * @param int $darkerThan
     *
     * @return bool
     */
    public function isDark($color = false, $darkerThan = 130)
    {
        // Get current color
        $color = ($color) ? $color : $this->_hex;

        // Calculate straight from rbg
        $r = hexdec($color[0] . $color[1]);
        $g = hexdec($color[2] . $color[3]);
        $b = hexdec($color[4] . $color[5]);

        return (($r * 299 + $g * 587 + $b * 114) / 1000 <= $darkerThan);
    }

    /**
     * Given a HEX value, returns a lighter color.
     * If no desired amount provided, then the color halfway between given HEX and white will be returned.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function lighten($amount = self::DEFAULT_ADJUST)
    {
        // Lighten
        $lighterHSL = $this->_lighten($this->getHsl(), $amount);
        $lighterHSL['a'] = $this->alpha;

        return $this->setHsl($lighterHSL);
    }

    /**
     * Given a HEX value, returns a darker color.
     * If no desired amount provided, then the color halfway between given HEX and black will be returned
     *
     * @param int $amount
     *
     * @return $this
     */
    public function darken($amount = self::DEFAULT_ADJUST)
    {
        // Darken
        $darkerHSL = $this->_darken($this->getHsl(), $amount);
        $darkerHSL['a'] = $this->alpha;

        return $this->setHsl($darkerHSL);
    }

    /**
     * Returns the complimentary color
     *
     * @return $this
     *
     */
    public function complementary()
    {
        // Get our HSL
        $hsla = $this->_hsl;

        // Adjust Hue 180 degrees
        $hsla['h'] += ($hsla['h'] > 180) ? -180 : 180;
        $hsla['a'] = $this->alpha;

        return $this->setHsl($hsla);
    }

    /**
     * @return $this
     */
    public function invert()
    {
        $rgb = $this->_rgb;
        $rgba = [
            'r' => 255 - $rgb['r'],
            'g' => 255 - $rgb['g'],
            'b' => 255 - $rgb['b'],
            'a' => $this->alpha,
        ];

        $this->_setRgbaColor($rgba);

        return $this;
    }

    /**
     * Given an additional color, returns a mixed color. If no desired amount provided, then the color mixed by this ratio
     *
     * @param mixed $color2 Secondary color to mix with
     * @param int $amount = -100..0..+100
     *
     * @return string mixed HEX value
     */
    public function mix($color2, $amount = 0)
    {
        $newColor = new static($color2);
        $mixed = $this->_mix($this->getRgb(), $newColor->getRgb(), $amount);
        $mixed['a'] = $this->alpha;

        $this->_setRgbaColor($mixed);

        return $this;
    }

    /**
     * Creates an array with two shades that can be used to make a gradient
     *
     * @param int $amount Optional percentage amount you want your contrast color
     *
     * @return array An array with a 'light' and 'dark' index
     */
    public function getGradientArray($amount = self::DEFAULT_ADJUST)
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
     * Returns the cross browser CSS3 gradient
     *
     * @param int     $amount Optional: percentage amount to light/darken the gradient
     * @param bool $vintageBrowsers Optional: include vendor prefixes for browsers that almost died out already
     * @param string $prefix Optional: prefix for every lines
     * @param string $suffix Optional: suffix for every lines
     *
     * @return string CSS3 gradient for chrome, safari, firefox, opera and IE10
     */
    public function getCssGradient($amount = self::DEFAULT_ADJUST, $vintageBrowsers = FALSE, $suffix = '', $prefix = '')
    {
        // Get the recommended gradient
        $g = $this->getGradientArray($amount);

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
     * Assign current color
     *
     * @param array $rgba
     * @param array $hsl
     */
    private function _setRgbaColor($rgba, $hsl = null)
    {
        $rgb = ['r' => $rgba['r'], 'g' => $rgba['g'], 'b' => $rgba['b']];

        if ($hsl) {
            if (isset($hsl['a'])) {
                unset($hsl['a']);
            }
            $this->_hsl = self::rgbToHsl($rgb);
        } else {
            $this->_hsl = self::rgbToHsl($rgb);
        }
        $this->_hex = self::rgbToHex($rgb);
        $this->_rgb = $rgb;
        $this->_alpha = isset($rgba['a']) ? $rgba['a'] : null;
    }

    /**
     * You need to check if you were given a good hex string
     *
     * @param string $hex
     * @param bool   $ignoreError
     *
     * @return array RGBA-array
     */
    private static function _checkHex($hex, $ignoreError = false)
    {
        $len = strlen($hex);
        // Strip # sign is present
        if (($len === 4 || $len === 5 || $len === 7 || $len === 9) && $hex[0] === '#') {
            $hex = substr($hex, 1);
            $len--;
        }
        if (strspn($hex, '01234567890abcdef') === $len) {
            if ($len === 3) {
                return ['r' => hexdec($hex[0] . $hex[0]), 'g' => hexdec($hex[1] . $hex[1]), 'b' => hexdec($hex[2] . $hex[2]), 'a' => null];
            }
            if ($len === 4) {
                return ['r' => hexdec($hex[0] . $hex[0]), 'g' => hexdec($hex[1] . $hex[1]), 'b' => hexdec($hex[2] . $hex[2]), 'a' => hexdec($hex[3] . $hex[3]) / 255];
            }
            if ($len === 6) {
                return ['r' => hexdec(substr($hex, 0, 2)), 'g' => hexdec(substr($hex, 2, 2)), 'b' => hexdec(substr($hex, 4, 2)), 'a' => null];
            }
            if ($len === 8) {
                return ['r' => hexdec(substr($hex, 0, 2)), 'g' => hexdec(substr($hex, 2, 2)), 'b' => hexdec(substr($hex, 4, 2)), 'a' => hexdec(substr($hex, 6, 2)) / 255];
            }
        }
        if (!$ignoreError) {
            self::_error(self::ERROR_HEX_FORMAT);
        }

        return null;
    }

    /**
     * @param array $rgb
     * @param bool  $ignoreError
     *
     * @return array
     */
    private static function _checkRgb($rgb, $ignoreError = false)
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
        if (!isset($rgb['r'], $rgb['g'], $rgb['b']) && !$ignoreError) {
            self::_error(self::ERROR_RGB_FORMAT);
            return null;
        }
        if (!isset($rgb['a'])) {
            $rgb['a'] = null;
        }
        $result = [];
        foreach($rgb as $key => $value) {
            if (in_array($key, ['r', 'g', 'b', 'a'], true)) {
                if ($value && is_string($value) && substr($value, -1) === '%') {
                    if ($key === 'a') {
                        $value = (float)$value / 100;
                    } else {
                        $value = (float)$value / 255;
                    }
                } else {
                    $value = ($key === 'a' && null === $value) ? null : (float)$value;
                }
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @param array $hsl
     * @param bool  $ignoreError
     *
     * @return array
     */
    private static function _checkHsl($hsl, $ignoreError = false)
    {
        // Make sure it's HSL
        if (isset($hsl[0], $hsl[1], $hsl[2])) {
            $hsl['h'] = $hsl[0];
            $hsl['s'] = $hsl[1];
            $hsl['l'] = $hsl[2];
            if (isset($hsl[3])) {
                $hsl['a'] = $hsl[3];
            }
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
        if (!isset($hsl['a'])) {
            $hsl['a'] = null;
        }
        if (!isset($hsl['h'], $hsl['s'], $hsl['l']) && !$ignoreError) {
            self::_error(self::ERROR_HSL_FORMAT);
            return null;
        }
        $result = [];
        foreach($hsl as $key => $value) {
            if (in_array($key, ['h', 's', 'l', 'a'], true)) {
                if ($value && is_string($value) && substr($value, -1) === '%') {
                    if ($key === 'h') {
                        self::_error(self::ERROR_HSL_FORMAT);
                        return null;
                    } else {
                        $value = (float)$value / 100;
                    }
                } else {
                    $value = ($key === 'a' && null === $value) ? null : (float)$value;
                }
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @param string $rgbStr
     * @param bool   $ignoreError
     *
     * @return null
     */
    private static function _checkRgbStr($rgbStr, $ignoreError = false)
    {
        if (preg_match('/^(rgba?)\((\d+\%?),(\d+\%?),(\d+\%?)(,([\d\.]+\%?))?\)$/', $rgbStr, $m)) {
            if ($m[1] === 'rgb' && isset($m[2], $m[3], $m[4])) {
                return [$m[2], $m[3], $m[4]];
            } elseif ($m[1] === 'rgba' && isset($m[2], $m[3], $m[4], $m[6])) {
                return [$m[2], $m[3], $m[4], $m[6]];
            }
        }
        if (!$ignoreError) {
            self::_error(self::ERROR_RGB_STR_FORMAT);
        }

        return null;
    }

    /**
     * @param string $hslStr
     * @param bool   $ignoreError
     *
     * @return null
     */
    private static function _checkHslStr($hslStr, $ignoreError = false)
    {
        if (preg_match('/^(hsla?)\((\d+),([\d\.]+\%?),([\d\.]+\%?)(,([\d\.]+\%?))?\)$/', $hslStr, $m)) {
            if ($m[1] === 'hsl' && isset($m[2], $m[3], $m[4])) {
                return [$m[2], $m[3], $m[4]];
            } elseif ($m[1] === 'hsla' && isset($m[2], $m[3], $m[4], $m[6])) {
                return [$m[2], $m[3], $m[4], $m[6]];
            }
        }
        if (!$ignoreError) {
            self::_error(self::ERROR_RGB_STR_FORMAT);
        }

        return null;
    }

    /**
     * @param mixed $value
     * @param float $base
     *
     * @return float
     */
    protected static function _checkValue($value, $base)
    {
        if ($value && !is_numeric($value)) {
            if (substr($value, -1) === '%') {
                return (float)$value / 100 * $base;
            }
            if ($value[0] === '#') {
                return hexdec(substr($value, 1));
            }
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
     * @param string $name
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
                return $this->_rgb['r'];
            case 'green':
            case 'g':
                return $this->_rgb['g'];
            case 'blue':
            case 'b':
                return $this->_rgb['b'];
            case 'alpha':
            case 'a':
                return $this->_alpha;
            case 'hue':
            case 'h':
                return $this->_hsl['h'];
            case 'saturation':
            case 's':
                return $this->_hsl['s'];
            case 'lightness':
            case 'l':
                return $this->_hsl['l'];
            case 'luma':
            case 'luminance':
                return $this->luma();
            default:
                static::_error(self::ERROR_NO_PROPERTY);
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        switch (strtolower($name)) {
            case 'red':
            case 'r':
                $rgba = $this->getRgba();
                $rgba['r'] = self::_checkValue($value, 255);
                $this->_setRgbaColor($rgba);
                break;
            case 'green':
            case 'g':
                $rgba = $this->getRgba();
                $rgba['g'] = self::_checkValue($value, 255);
                $this->_setRgbaColor($rgba);
                break;
            case 'blue':
            case 'b':
                $rgba = $this->getRgba();
                $rgba['b'] = self::_checkValue($value, 255);
                $this->_setRgbaColor($rgba);
                break;
            case 'alpha':
            case 'a':
                $rgba = $this->getRgba();
                $rgba['a'] = self::_checkValue($value, 1);
                $this->_setRgbaColor($rgba);
                break;
            case 'hue':
            case 'h':
                $hsl = $this->getHsla();
                $hsl['h'] = self::_checkValue($value, 360);
                $this->_setRgbaColor(static::hslToRgba($hsl), $hsl);
                break;
            case 'saturation':
            case 's':
                $hsl = $this->getHsla();
                $hsl['s'] = self::_checkValue($value, 1);
                $this->_setRgbaColor(static::hslToRgba($hsl), $hsl);
                break;
            case 'lightness':
            case 'light':
            case 'l':
                $hsl = $this->getHsla();
                $hsl['l'] = self::_checkValue($value, 1);
                $this->_setRgbaColor(static::hslToRgba($hsl), $hsl);
                break;
            default:
                static::_error(self::ERROR_NO_PROPERTY);
        }
    }

}

// EOF