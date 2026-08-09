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
 * Also, RGB-arrays can be with missed indexes:
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

    const ERRORS = [
        self::ERROR_COLOR_FORMAT    => 'Wrong color format',
        self::ERROR_HEX_FORMAT      => 'Wrong format of HEX string (required "#RRGGBB" or "#RGB")',
        self::ERROR_HEXA_FORMAT     => 'Wrong format of HEXA string (required "#RRGGBBAA" or "#RGBA")',
        self::ERROR_RGB_FORMAT      => 'Wrong format of RGB array (required ["r"=>R,"g"=>G,"b"=>B]',
        self::ERROR_RGBA_FORMAT     => 'Wrong format of RGBA array (required ["r"=>R,"g"=>G,"b"=>B,"a"=>A]',
        self::ERROR_HSL_FORMAT      => 'Wrong format of HSL array (required ["h"=>H,"s"=>S,"l"=>L]',
        self::ERROR_HSLA_FORMAT     => 'Wrong format of HSLA array (required ["h"=>H,"s"=>S,"l"=>L,"a"=>A]',
        self::ERROR_RGB_STR_FORMAT  => 'Wrong format of RGB_STR string (required "rgb(R,G,B)")',
        self::ERROR_RGBA_STR_FORMAT => 'Wrong format of RGBA_STR string (required "rgba(R,G,B,A)")',
        self::ERROR_NO_PROPERTY     => 'Unknown color property',
    ];

    private string $_hex;
    private array $_hsl;
    private array $_rgb;
    private ?float $_alpha;

    /** @var string|null prefix for hex(a) results */
    private ?string $_sharp = null;

    /**
     * Key case of the returned color arrays, CASE_LOWER or CASE_UPPER
     *
     * Inside the class the keys are ALWAYS lowercase, this setting is applied
     * by _out() at the very moment a public method returns an array
     *
     * @var int
     */
    private static int $keyCase = CASE_LOWER;

    protected static array $errors = [];

    /**
     * Auto darkens/lightens by 10% for sexily-subtle gradients.
     * Set this FALSE to adjust automatic shade to be between given color
     * and black (for darken) or white (for lighten)
     */
    const DEFAULT_ADJUST = 10;

    /**
     * AceColors constructor
     *
     * @param string|array $input
     *
     * @throws \RuntimeException
     */
    public function __construct($input = null)
    {
        $color = null;
        if ($input === '#') {
            $this->useSharp(true);
            $input = null;
        }
        if (null === $input) {
            $color = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 1.0];
            $this->setRgb($color);
        }
        elseif (is_string($input)) {
            if ($color = self::_checkHex($input, true)) {
                $this->setRgb($color);
                if ($this->_sharp === null && $input[0] === '#') {
                    $this->useSharp(true);
                }
            }
            elseif ($color = self::_checkRgbStr($input, true)) {
                $this->setRgb($color);
            }
            elseif ($color = self::_checkHslStr($input, true)) {
                $this->setHsl($color);
            }
        }
        elseif (is_array($input)) {
            if ($color = self::_checkRgb($input, true)) {
                $this->setRgb($color);
            }
            elseif ($color = self::_checkHsl($input, true)) {
                $this->setHsl($color);
            }
            else {
                static::_error(self::ERROR_COLOR_FORMAT, $input);
            }
        }
        if (empty($color)) {
            static::_error(self::ERROR_COLOR_FORMAT, $input);
        }
    }

    /**
     * @param $code
     * @param $param
     *
     * @throws \RuntimeException
     */
    protected static function _error($code, $param = null)
    {
        if (null === $param) {
            $param = '';
        }
        elseif (is_scalar($param)) {
            $param = ' -- ' . $param;
        }
        else {
            // an empty array is falsy, so it must be handled here too, otherwise
            // it would reach the string concatenation below as an array
            $param = ' -- ' . print_r($param, true);
        }
        if (!empty(self::ERRORS[$code])) {
            throw new \RuntimeException(self::ERRORS[$code] . $param);
        }
        throw new \RuntimeException('Unknown error' . $param);
    }

    /**
     * @param bool $sharp
     *
     * @return $this
     */
    public function useSharp(bool $sharp): AceColors
    {
        $this->_sharp = $sharp ? '#' : '';

        return $this;
    }

    /**
     * Sets the key case of all returned color arrays
     *
     * Affects the arrays of color channels only: hexToRgb(), hexToRgba(), hexToHsl(),
     * rgbToHsl(), hslToRgb(), hslToRgba(), hslaToRgba(), getRgb(), getRgba(), getHsl(),
     * getHslA(). Methods returning strings are not affected, neither is
     * getGradientArray() whose 'light'/'dark' keys are roles, not color channels
     *
     * The setting is global for the process, restore it with setKeyCase(CASE_LOWER)
     *
     * @param int $case CASE_LOWER (default) or CASE_UPPER
     *
     * @return void
     */
    public static function setKeyCase(int $case): void
    {
        self::$keyCase = ($case === CASE_UPPER) ? CASE_UPPER : CASE_LOWER;
    }

    /**
     * Returns the current key case setting
     *
     * @return int
     */
    public static function getKeyCase(): int
    {
        return self::$keyCase;
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
    public function setHex(string $hex): AceColors
    {
        $color = self::_checkHex($hex);

        return $this->setRgb($color);
    }

    /**
     * Given an RGB(A) array and set color
     *
     * @param array $rgb Array in RGB or RGBA format
     *
     * @return $this
     */
    public function setRgb(array $rgb): AceColors
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
    public function setRgbStr(string $rgbStr): AceColors
    {
        $rgba = self::_checkRgbStr($rgbStr, true);
        if ($rgba) {
            $this->setRgb($rgba);
        }
        return $this;
    }

    /**
     * Given an HSL(A) array and set color
     *
     * @param array $hsl Array in HSL or HSLA (with alpha) format
     *
     * @return $this
     */
    public function setHsl(array $hsl): AceColors
    {
        $hsl = self::_checkHsl($hsl);
        if ($hsl) {
            if (isset($hsl['a'])) {
                $this->_setRgbaColor(static::_hslToRgba($hsl), $hsl);
            }
            else {
                $this->_setRgbaColor(static::_hslToRgb($hsl), $hsl);
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
    public function setRed($value): AceColors
    {
        $this->__set('red', $value);
        $this->_setRgbaColor($this->_rgba());

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setGreen($value): AceColors
    {
        $this->__set('green', $value);
        $this->_setRgbaColor($this->_rgba());

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setBlue($value): AceColors
    {
        $this->__set('blue', $value);
        $this->_setRgbaColor($this->_rgba());

        return $this;
    }

    /**
     * Set value of alpha channel
     *
     * @param $value
     *
     * @return $this
     */
    public function setAlpha($value): AceColors
    {
        if (is_string($value) && substr($value, -1) === '%') {
            $value = (float)$value / 100;
        }
        elseif ($value > 1 && $value <= 100) {
            $value /= 100;
        }
        else {
            $value = (float)$value;
        }
        $this->_alpha = $value;
        $this->_setRgbaColor($this->_rgba());

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setHue($value): AceColors
    {
        $hsl = $this->_hsl;
        $hsl['h'] = $this->_checkValue($value, 360);
        return $this->setHsl($hsl);
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setSaturation($value): AceColors
    {
        if ($value > 1 && $value <= 100) {
            $value /= 100;
        }
        $hsl = $this->_hsl;
        $hsl['s'] = $value;
        return $this->setHsl($hsl);
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setLightness($value): AceColors
    {
        if ($value > 1 && $value <= 100) {
            $value /= 100;
        }
        $hsl = $this->_hsl;
        $hsl['l'] = $value;
        return $this->setHsl($hsl);
    }

    /* *****************************************************
     * Methods return the current color in different formats
     */

    /**
     * Returns current color as HEX-string
     *
     * @return string
     */
    public function getHex(): string
    {
        return $this->_sharp . $this->_hex;
    }

    /**
     * Returns current color as HEXA-string
     *
     * @return string
     */
    public function getHexa(): string
    {
        $hex = $this->_hex;
        if (null === $this->_alpha) {
            $hex .= 'ff';
        }
        else {
            $hex .= str_pad(dechex(round($this->_alpha * 255)), 2, '0', STR_PAD_LEFT);
        }
        return $this->_sharp . $hex;
    }

    /**
     * Returns current color as RGB-array
     *
     * @return array
     */
    public function getRgb(): array
    {
        return static::_out($this->_rgb);
    }

    /**
     * Returns current color as RGBA-array
     *
     * @return array
     */
    public function getRgba(): array
    {
        return static::_out($this->_rgba());
    }

    /**
     * Current color as an RGBA-array, always in lowercase keys, for internal use
     *
     * @return array
     */
    protected function _rgba(): array
    {
        $rgba = $this->_rgb;
        $rgba['a'] = (null === $this->_alpha) ? 1.0 : $this->_alpha;

        return $rgba;
    }

    /**
     * Returns current color as string 'rgb(...)'
     *
     * @return string
     */
    public function getRgbStr(): string
    {
        return static::rgbToStr($this->_rgb);
    }

    /**
     * Returns current color as string 'rgba(...)'
     *
     * @return string
     */
    public function getRgbaStr(): string
    {
        return static::rgbaToStr($this->_rgba());
    }

    /**
     * Returns current color as HSL array
     *
     * @return array
     */
    public function getHsl(): array
    {
        return static::_out($this->_hsl);
    }

    /**
     * Returns current color as HSLA array
     *
     * @return array
     */
    public function getHslA(): array
    {
        return static::_out($this->_hsla());
    }

    /**
     * Current color as an HSLA-array, always in lowercase keys, for internal use
     *
     * @return array
     */
    protected function _hsla(): array
    {
        $hsla = $this->_hsl;
        $hsla['a'] = (null === $this->_alpha) ? 1.0 : $this->_alpha;

        return $hsla;
    }

    /**
     * Returns current color as string 'hsl(...)'
     *
     * @return string
     */
    public function getHslStr(): string
    {
        return static::hslToStr($this->_hsl);
    }

    /**
     * Returns current color as string 'hsla(...)'
     *
     * @return string
     */
    public function getHslaStr(): string
    {
        return static::hslaToStr($this->_hsla());
    }


    /* ***************************************
     * Convert colors form one format to other
     */

    /**
     * Given a HEX string returns a RGB array equivalent
     *
     * The alpha channel is dropped, use hexToRgba() to keep it.
     * Throws a RuntimeException on a malformed string
     *
     * @param string $color
     *
     * @return array RGB associative array
     */
    public static function hexToRgb(string $color): array
    {
        // Sanity check
        $rgb = self::_checkHex($color);
        unset($rgb['a']);

        return static::_out($rgb);
    }

    /**
     * Given a HEX(A) string returns a RGBA array equivalent
     *
     * The alpha is null when the input string carries no alpha channel
     *
     * @param string $color
     *
     * @return array RGBA associative array
     */
    public static function hexToRgba(string $color): array
    {
        // Sanity check
        return static::_out(self::_checkHex($color));
    }

    /**
     * Given a HEX string returns a HSL array equivalent
     *
     * @param $color
     *
     * @return array HSL associative array
     */
    public static function hexToHsl($color): array
    {
        // Sanity check
        $rgbColor = self::_checkHex($color);

        return static::_out(static::_rgbToHsl($rgbColor));
    }

    /**
     *  Given an RGB(A) associative array returns the equivalent HEX string
     *
     * @param array $rgb
     * @param bool $alpha
     *
     * @return string HEX(A)-string
     */
    public static function rgbToHex(array $rgb, bool $alpha = false): string
    {
        $color = self::_checkRgb($rgb);

        // Convert to hex. Make sure we get 2 digits for decimals.
        // The channels are floats, the cast is explicit to avoid an implicit-conversion
        // deprecation on PHP 8.1+. Note it truncates, as dechex() did on its own before
        $r = (int)$color['r'];
        $g = (int)$color['g'];
        $b = (int)$color['b'];

        $hr = ($r < 16) ? '0' . dechex($r) : dechex($r);
        $hg = ($g < 16) ? '0' . dechex($g) : dechex($g);
        $hb = ($b < 16) ? '0' . dechex($b) : dechex($b);
        if (!$alpha) {
            return $hr . $hg . $hb;
        }
        $a = (int)(255 * $color['a']);

        return $hr . $hg . $hb . (($a < 16) ? '0' . dechex($a) : dechex($a));
    }

    /**
     *  Given an RGBA associative array returns the equivalent HEX string
     *  The alpha channel is dropped, use rgbaToHexa() to keep it
     *
     * @param array $rgba
     *
     * @return string HEX string RRGGBB
     */
    public static function rgbaToHex(array $rgba): string
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
    public static function rgbaToHexa(array $rgba): string
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
    public static function rgbToStr(array $rgb): string
    {
        $color = self::_checkRgb($rgb);

        return 'rgb(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ')';
    }

    /**
     *  Given an RGBA associative array and returns the string as rgba() function
     *
     * @param array $rgba
     *
     * @return string As 'rgba(R, G, B, A)'
     */
    public static function rgbaToStr(array $rgba): string
    {
        $color = self::_checkRgb($rgba);

        return 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ',' . str_replace(',', '.', '' . $color['a']) . ')';
    }

    /**
     * Given a RGB array returns a HSL array equivalent
     *
     * @param array $color
     *
     * @return array HSL associative array
     */
    public static function rgbToHsl(array $color): array
    {
        return static::_out(static::_rgbToHsl($color));
    }

    /**
     * The same as rgbToHsl() but always in lowercase keys, for internal use
     *
     * @param array $color
     *
     * @return array HSL associative array
     */
    protected static function _rgbToHsl(array $color): array
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

        $H = 0;
        $S = 0;
        if ($delMax > 0) {
            if ($L < 0.5) {
                $S = $delMax / ($varMax + $varMin);
            }
            else {
                $S = $delMax / (2 - $varMax - $varMin);
            }

            $delR = ((($varMax - $R) / 6) + ($delMax / 2)) / $delMax;
            $delG = ((($varMax - $G) / 6) + ($delMax / 2)) / $delMax;
            $delB = ((($varMax - $B) / 6) + ($delMax / 2)) / $delMax;

            if ($R === $varMax) {
                $H = $delB - $delG;
            }
            elseif ($G === $varMax) {
                $H = (1 / 3) + $delR - $delB;
            }
            elseif ($B === $varMax) {
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
    public static function hslToHex(array $hsl): string
    {
        return static::rgbToHex(static::_hslToRgb($hsl));
    }

    /**
     *  Given an HSL associative array returns the equivalent RGB array
     *
     * @param array $hsl
     *
     * @return array RGB-array
     */
    public static function hslToRgb(array $hsl): array
    {
        return static::_out(static::_hslToRgb($hsl));
    }

    /**
     * The same as hslToRgb() but always in lowercase keys, for internal use
     *
     * @param array $hsl
     *
     * @return array RGB-array
     */
    protected static function _hslToRgb(array $hsl): array
    {
        $rgb = static::_hslToRgba($hsl);
        // the key is dropped while it is still lowercase, before _out() renames it
        unset($rgb['a']);

        return $rgb;
    }

    /**
     *  Given an HSL associative array returns the equivalent RGBA array
     *
     * @param array $hsl
     *
     * @return array RGBA array
     */
    public static function hslToRgba(array $hsl): array
    {
        return static::_out(static::_hslToRgba($hsl));
    }

    /**
     * The same as hslToRgba() but always in lowercase keys, for internal use
     *
     * @param array $hsl
     *
     * @return array RGBA array
     */
    protected static function _hslToRgba(array $hsl): array
    {
        // Make sure it's HSL
        $hsl = self::_checkHsl($hsl);

        list($H, $S, $L) = [$hsl['h'] / 360, $hsl['s'], $hsl['l']];

        if ((float)$S === 0.0) {
            $r = $L * 255;
            $g = $L * 255;
            $b = $L * 255;
        }
        else {
            if ($L < 0.5) {
                $var_2 = $L * (1 + $S);
            }
            else {
                $var_2 = ($L + $S) - ($S * $L);
            }

            $var_1 = 2 * $L - $var_2;

            $r = round(255 * self::_hue2rgb($var_1, $var_2, $H + (1 / 3)));
            $g = round(255 * self::_hue2rgb($var_1, $var_2, $H));
            $b = round(255 * self::_hue2rgb($var_1, $var_2, $H - (1 / 3)));
        }
        return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => $hsl['a'] ?? null];
    }


    /**
     *  Given an HSL associative array returns the equivalent RGBA array
     *
     * @param array $hsla
     *
     * @return array RGBA array
     */
    public static function hslaToRgba(array $hsla): array
    {
        return static::_out(static::_hslToRgba($hsla));
    }

    /**
     *  Given an HSL associative array returns the equivalent HEX string
     *
     * @param array $hsla
     *
     * @return string HEX string RRGGBB
     */
    public static function hslaToHex(array $hsla): string
    {
        return static::rgbToHex(static::_hslToRgb($hsla));
    }

    /**
     *  Given a HSL associative array returns the equivalent HEX string
     *
     * @param array $hsla
     *
     * @return string HEX string RRGGBBAA
     */
    public static function hslaToHexa(array $hsla): string
    {
        return static::rgbaToHexa(static::_hslToRgba($hsla));
    }

    /**
     *  Given an HSL associative array and returns the string as 'hsl(...)' function
     *
     * @param array $hsl
     *
     * @return string As 'hsl(R, G, B)'
     */
    public static function hslToStr(array $hsl): string
    {
        $color = self::_checkHsl($hsl);

        return 'hsl(' . $color['h'] . ',' . ($color['s'] * 100) . '%,' . ($color['l'] * 100) . '%)';
    }

    /**
     *  Given an HSLA associative array and returns the string as 'hsl(...)' function
     *
     * @param array $hsl
     *
     * @return string As 'hsla(H, S, L, A)'
     */
    public static function hslaToStr(array $hsl): string
    {
        $color = self::_checkHsl($hsl);

        return 'hsla(' . $color['h'] . ',' . ($color['s'] * 100) . '%,' . ($color['l'] * 100) . '%,' . str_replace(',', '.', '' . $color['a']) . ')';
    }


    /* ***************************************
     * Make new color objects
     */

    /**
     * Make new color object with the same color
     *
     * @return $this
     */
    public function cloneColor(): AceColors
    {
        return clone $this;
    }

    /**
     * Make new color object with inverted color
     *
     * @return $this
     */
    public function makeInverted(): AceColors
    {
        return $this->cloneColor()->invert();
    }

    /**
     * Make new color object with complementary color
     *
     * @return $this
     */
    public function makeComplimentary(): AceColors
    {
        return $this->cloneColor()->complementary();
    }

    /**
     * Make new color object with darker color
     *
     * @param int $amount
     *
     * @return $this
     */
    public function makeDarker(int $amount = self::DEFAULT_ADJUST): AceColors
    {
        return $this->cloneColor()->darken($amount);
    }

    /**
     * @param int $amount
     *
     * @return $this
     */
    public function makeLighter(int $amount = self::DEFAULT_ADJUST): AceColors
    {
        return $this->cloneColor()->lighten($amount);
    }

    /* ***************************************
     * Other methods
     */

    /**
     * Relative luminance
     *
     * The relative brightness of any point in a colorspace, normalized to 0 for darkest black and 1 for lightest white
     * Uses SMPTE C / Rec. 709 coefficients, as recommended in WCAG 2.0.
     *
     * @see https://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef
     *
     * @return float
     */
    public function luma(): float
    {
        $rgb = $this->_rgb;
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $R = ($r <= 0.03928) ? ($r / 12.92) : pow(($r + 0.055)/1.055, 2.4);
        $G = ($g <= 0.03928) ? ($g / 12.92) : pow(($g + 0.055)/1.055, 2.4);
        $B = ($b <= 0.03928) ? ($b / 12.92) : pow(($b + 0.055)/1.055, 2.4);

        return 0.2126 * $R + 0.7152 * $G + 0.0722 * $B;
    }

    /**
     * Returns whether given color is considered "light"
     *
     * @param mixed $color
     *
     * @param int $lighterThan
     *
     * @return bool
     */
    public function isLight($color = null, int $lighterThan = 130): bool
    {
        return $this->_compareLevel($color, $lighterThan) > 0;
    }

    /**
     * Returns whether a given color is considered "dark"
     *
     * @param mixed $color
     *
     * @param int $darkerThan
     *
     * @return bool
     */
    public function isDark($color = null, int $darkerThan = 130): bool
    {
        return $this->_compareLevel($color, $darkerThan) <= 0;
    }

    /**
     * Given a HEX value, returns a lighter color.
     * If no desired amount provided, then the color halfway between given HEX and white will be returned.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function lighten(int $amount = self::DEFAULT_ADJUST): AceColors
    {
        // Lighten
        $lighterHSL = $this->_lighten($this->_hsl, $amount);
        $lighterHSL['a'] = $this->_alpha;

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
    public function darken(int $amount = self::DEFAULT_ADJUST): AceColors
    {
        // Darken
        $darkerHSL = $this->_darken($this->_hsl, $amount);
        $darkerHSL['a'] = $this->_alpha;

        return $this->setHsl($darkerHSL);
    }

    /**
     * Increases the saturation of the current color
     *
     * The amount can be given as percents ('20%', or a number with an absolute value greater than 1)
     * or as a fraction of the 0.0 - 1.0 range (0.2). A negative amount desaturates
     *
     * @param int|float|string $amount
     *
     * @return $this
     */
    public function saturate($amount = self::DEFAULT_ADJUST): AceColors
    {
        $hsl = $this->_hsl;
        $saturation = $hsl['s'] + self::_saturationAmount($amount);

        $hsl['s'] = max(0.0, min(1.0, $saturation));
        $hsl['a'] = $this->_alpha;

        return $this->setHsl($hsl);
    }

    /**
     * Decreases the saturation of the current color
     *
     * Takes the same amount formats as saturate(), a negative amount saturates
     *
     * @param int|float|string $amount
     *
     * @return $this
     */
    public function desaturate($amount = self::DEFAULT_ADJUST): AceColors
    {
        return $this->saturate(-self::_saturationAmount($amount));
    }

    /**
     * Returns the complimentary color
     *
     * @return $this
     *
     */
    public function complementary(): AceColors
    {
        // Get our HSL
        $hsla = $this->_hsl;

        // Adjust Hue 180 degrees, keeping it in the 0 - 360 range
        $hsla['h'] = fmod($hsla['h'] + 180, 360);
        $hsla['a'] = $this->_alpha;

        return $this->setHsl($hsla);
    }

    /**
     * @return $this
     */
    public function invert(): AceColors
    {
        $rgb = $this->_rgb;
        $rgba = [
            'r' => 255 - $rgb['r'],
            'g' => 255 - $rgb['g'],
            'b' => 255 - $rgb['b'],
            'a' => $this->_alpha,
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
    public function mix($color2, int $amount = 0)
    {
        if ($color2 instanceof static) {
            $newColor = $color2;
        } else {
            $newColor = new static($color2);
        }
        // private is visible between instances of the same class, so no getter is needed
        $mixed = $this->_mix($this->_rgb, $newColor->_rgb, $amount);
        $mixed['a'] = $this->_alpha;

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
    public function getGradientArray(int $amount = self::DEFAULT_ADJUST): array
    {
        // The missing shade is calculated on a copy, so the current color stays unchanged.
        // Both values are bare HEX strings without '#', getCssGradient() adds the sign itself
        if ($this->isLight()) {
            return ['light' => $this->_hex, 'dark' => $this->makeDarker($amount)->_hex];
        }

        return ['light' => $this->makeLighter($amount)->_hex, 'dark' => $this->_hex];
    }

    /**
     * Returns the cross browser CSS3 gradient
     *
     * @param int $amount Optional: percentage amount to light/darken the gradient
     * @param bool $vintageBrowsers Optional: include vendor prefixes for browsers that almost died out already
     * @param string $prefix Optional: prefix for every lines
     * @param string $suffix Optional: suffix for every lines
     *
     * @return string CSS3 gradient for chrome, safari, firefox, opera and IE10
     */
    public function getCssGradient(int $amount = self::DEFAULT_ADJUST, bool $vintageBrowsers = FALSE, string $suffix = '', string $prefix = ''): string
    {
        // Get the recommended gradient
        $g = $this->getGradientArray($amount);

        $css = '';
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


    /* ***************************************
     * Private methods
     */

    /**
     * Applies the configured key case to a color array
     *
     * This is the ONE and ONLY place where the key case is applied. Everything inside
     * the class works with lowercase keys, so no internal code may call a public method
     * that passes its result through _out()
     *
     * @param array $color
     *
     * @return array
     */
    protected static function _out(array $color): array
    {
        return (self::$keyCase === CASE_UPPER) ? array_change_key_case($color, CASE_UPPER) : $color;
    }

    private function _compareLevel($color, $compareLevel): int
    {
        if ($color) {
            $color = new static($color);
            $hex = $color->getHex();
            if ($hex[0] === '#') {
                $hex = substr($hex, 1);
            }
        } else {
            // Get current color
            $hex = $this->_hex;
        }

        // Calculate straight from rbg
        $r = hexdec($hex[0] . $hex[1]);
        $g = hexdec($hex[2] . $hex[3]);
        $b = hexdec($hex[4] . $hex[5]);

        $value = ($r * 299 + $g * 587 + $b * 114) / 1000;

        if ($value === (float)$compareLevel) {
            return 0;
        }
        return ($value > $compareLevel) ? 1 : -1;
    }

    /**
     * Darkens a given HSL array
     *
     * @param array $hsl
     * @param int $amount
     *
     * @return array $hsl
     */
    private function _darken(array $hsl, int $amount = self::DEFAULT_ADJUST): array
    {
        // Check if we were provided a number
        if ($amount) {
            $hsl['l'] = ($hsl['l'] * 100) - $amount;
            $hsl['l'] = ($hsl['l'] < 0) ? 0 : $hsl['l'] / 100;
        } else {
            // We need to find out how much to darken
            $hsl['l'] /= 2;
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
    private function _lighten(array $hsl, int $amount = self::DEFAULT_ADJUST): array
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
     * Mix 2 RGB colors and return an RGB color
     *
     * @param array $rgb1
     * @param array $rgb2
     * @param int $amount ranged -100..0..+100
     *
     * @return array $rgb
     */
    private function _mix(array $rgb1, array $rgb2, int $amount = 0): array
    {
        $r1 = ($amount + 100) / 100;
        $r2 = 2 - $r1;

        $rMix = (($rgb1['r'] * $r1) + ($rgb2['r'] * $r2)) / 2;
        $gMix = (($rgb1['g'] * $r1) + ($rgb2['g'] * $r2)) / 2;
        $bMix = (($rgb1['b'] * $r1) + ($rgb2['b'] * $r2)) / 2;

        return ['r' => $rMix, 'g' => $gMix, 'b' => $bMix];
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
     * @param array|null $hsl
     */
    private function _setRgbaColor(array $rgba, ?array $hsl = null)
    {
        $rgb = ['r' => $rgba['r'], 'g' => $rgba['g'], 'b' => $rgba['b']];

        if ($hsl) {
            $this->_hsl = $hsl;
        } else {
            $this->_hsl = static::_rgbToHsl($rgb);
        }
        $this->_hex = static::rgbToHex($rgb);
        $this->_rgb = $rgb;
        $this->_alpha = $rgba['a'] ?? null;
    }

    /**
     * You need to check if you were given a good hex string
     *
     * @param string $hex
     * @param bool $ignoreError
     *
     * @return array RGBA-array
     */
    private static function _checkHex(string $hex, bool $ignoreError = false): ?array
    {
        $len = strlen($hex);
        // Strip # sign is present
        if (($len === 4 || $len === 5 || $len === 7 || $len === 9) && $hex[0] === '#') {
            $hex = '' . substr($hex, 1);
            $len--;
        }
        if (strspn(strtolower($hex), '01234567890abcdef') === $len) {
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
            static::_error(self::ERROR_HEX_FORMAT);
        }

        return null;
    }

    /**
     * @param array $rgb
     * @param bool $ignoreError
     *
     * @return array
     */
    private static function _checkRgb(array $rgb, bool $ignoreError = false): ?array
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
            if ($ignoreError) {
                return null;
            }
            static::_error(self::ERROR_RGB_FORMAT);
        }
        if (!isset($rgb['a'])) {
            $rgb['a'] = null;
        }
        $result = [];
        foreach($rgb as $key => $value) {
            if (in_array($key, ['r', 'g', 'b', 'a'], true)) {
                if ($value && is_string($value) && substr($value, -1) === '%') {
                    $value = (float)$value / 100;
                    if ($key !== 'a') {
                        $value *= 255;
                    }
                }
                else {
                    $value = ($key === 'a' && null === $value) ? null : (float)$value;
                }
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @param array $hsl
     * @param bool $ignoreError
     *
     * @return array
     */
    private static function _checkHsl(array $hsl, bool $ignoreError = false): array
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
        if (!isset($hsl['h'], $hsl['s'], $hsl['l'])) {
            if ($ignoreError) {
                return [];
            }
            static::_error(self::ERROR_HSL_FORMAT);
        }
        if (!isset($hsl['a'])) {
            $hsl['a'] = null;
        }
        $result = [];
        foreach($hsl as $key => $value) {
            if (in_array($key, ['h', 's', 'l', 'a'], true)) {
                if ($value && is_string($value) && substr($value, -1) === '%') {
                    if ($key === 'h') {
                        static::_error(self::ERROR_HSL_FORMAT);
                    }
                    $value = (float)$value / 100;
                }
                else {
                    $value = ($key === 'a' && null === $value) ? null : (float)$value;
                }
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @param string $rgbStr
     * @param bool $ignoreError
     *
     * @return array|null
     */
    private static function _checkRgbStr(string $rgbStr, bool $ignoreError = false): ?array
    {
        if (preg_match('/^(rgba?)\s*\(\s*(\d+%?)\s*,\s*(\d+%?)\s*,\s*(\d+%?)\s*(?:,\s*([\d\.]+%?)\s*)?\)$/', $rgbStr, $m)) {
            if (($m[1] === 'rgb' || $m[1] === 'rgba') && isset($m[2], $m[3], $m[4])) {
                $res = ['r' => $m[2], 'g' => $m[3], 'b' => $m[4]];
                if (isset($m[5])) {
                    $res['a'] = $m[5];
                }
                return $res;
            }
        }
        if (!$ignoreError) {
            static::_error(self::ERROR_RGB_STR_FORMAT);
        }

        return null;
    }

    /**
     * @param string $hslStr
     * @param bool $ignoreError
     *
     * @return array|null
     */
    private static function _checkHslStr(string $hslStr, bool $ignoreError = false): ?array
    {
        if (preg_match('/^(hsla?)\s*\(\s*(\d+)\s*,\s*([\d\.]+%?)\s*,\s*([\d\.]+%?)\s*(?:,\s*([\d\.]+%?)\s*)?\)$/', $hslStr, $m)) {
            if (($m[1] === 'hsl' || $m[1] === 'hsla') && isset($m[2], $m[3], $m[4])) {
                $res = ['h' => $m[2], 's' => $m[3], 'l' => $m[4]];
                if (isset($m[5])) {
                    $res['a'] = $m[5];
                }
                return $res;
            }
        }
        if (!$ignoreError) {
            static::_error(self::ERROR_RGB_STR_FORMAT);
        }

        return null;
    }

    /**
     * @param mixed $value
     * @param float $base
     *
     * @return float
     */
    protected static function _checkValue($value, float $base)
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
     * Normalizes a saturation amount to a signed fraction of the 0.0 - 1.0 range
     *
     * Percent strings ('20%') and numbers with an absolute value greater than 1 are treated
     * as percents, everything else as a fraction. The sign is always preserved
     *
     * @param mixed $amount
     *
     * @return float ranged -1.0 .. +1.0
     */
    private static function _saturationAmount($amount): float
    {
        if (is_string($amount) && substr($amount, -1) === '%') {
            $amount = (float)$amount / 100;
        }
        elseif (is_numeric($amount) && abs($amount) > 1) {
            $amount = (float)$amount / 100;
        }
        else {
            $amount = (float)static::_checkValue($amount, 1);
        }

        return max(-1.0, min(1.0, $amount));
    }

    /**
     * Converts object into its hex string representation with '#'
     *
     * @return string Colors
     */
    public function __toString()
    {
        return ($this->_sharp ? '' : '#') . $this->getHex();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
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
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        switch (strtolower($name)) {
            case 'red':
            case 'r':
                $rgba = $this->_rgba();
                $rgba['r'] = static::_checkValue($value, 255);
                $this->_setRgbaColor($rgba);
                break;
            case 'green':
            case 'g':
                $rgba = $this->_rgba();
                $rgba['g'] = static::_checkValue($value, 255);
                $this->_setRgbaColor($rgba);
                break;
            case 'blue':
            case 'b':
                $rgba = $this->_rgba();
                $rgba['b'] = static::_checkValue($value, 255);
                $this->_setRgbaColor($rgba);
                break;
            case 'alpha':
            case 'a':
                $rgba = $this->_rgba();
                $rgba['a'] = static::_checkValue($value, 1);
                $this->_setRgbaColor($rgba);
                break;
            case 'hue':
            case 'h':
                $hsl = $this->_hsla();
                $hsl['h'] = static::_checkValue($value, 360);
                $this->_setRgbaColor(static::_hslToRgba($hsl), $hsl);
                break;
            case 'saturation':
            case 's':
                $hsl = $this->_hsla();
                $hsl['s'] = static::_checkValue($value, 1);
                $this->_setRgbaColor(static::_hslToRgba($hsl), $hsl);
                break;
            case 'lightness':
            case 'light':
            case 'l':
                $hsl = $this->_hsla();
                $hsl['l'] = static::_checkValue($value, 1);
                $this->_setRgbaColor(static::_hslToRgba($hsl), $hsl);
                break;
            default:
                static::_error(self::ERROR_NO_PROPERTY);
        }
    }

}

// EOF