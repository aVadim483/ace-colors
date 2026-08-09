# AceColors

[![tests](https://github.com/aVadim483/ace-colors/actions/workflows/tests.yml/badge.svg)](https://github.com/aVadim483/ace-colors/actions/workflows/tests.yml)

A set of helpers for converting and manipulating colors. Converts between HEX, RGB(A) and HSL(A)
in any direction, and lets you lighten, darken, saturate, mix and invert a color.

Requires PHP 7.4 or above and has no runtime dependencies.

## Install

The package is not published on Packagist yet, so add the repository explicitly:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/aVadim483/ace-colors" }
    ],
    "require": {
        "avadim/ace-colors": "dev-master"
    }
}
```

```
$ composer require avadim/ace-colors:dev-master
```

Composer is not required — the package ships its own autoloader:

```php
require_once 'path/to/ace-colors/src/autoload.php';
```

## Sample usage

```php
use avadim\AceColors\AceColors;

// black
$color = new AceColors();

// red, in three equivalent notations
$color = new AceColors('ff0000');
$color = new AceColors('f00');
$color = new AceColors('#ff0000');

// ...and from the other formats
$color = new AceColors('rgba(255, 0, 0, 0.5)');
$color = new AceColors('hsl(0, 100%, 50%)');
$color = new AceColors(['r' => 255, 'g' => 0, 'b' => 0]);

// darken the color and print the hex code
echo (new AceColors('#3388cc'))->darken()->getHex();       // #296da3

// setters are chainable and modify the color in place
echo (new AceColors())
    ->setRed(51)
    ->setGreen(136)
    ->setBlue(204)
    ->setAlpha(0.8)
    ->getHexa();                                           // 3388cccc

// static converters need no instance
print_r(AceColors::hexToRgb('#3388cc'));                   // ['r' => 51, 'g' => 136, 'b' => 204]
echo AceColors::hslToHex(['h' => 210, 's' => 0.5, 'l' => 0.4]);   // 336699
```

If the `'#'` prefix was present in the constructor, it is kept in the hex output; otherwise it is
omitted. Use `useSharp(true|false)` to control that explicitly. Casting the object to a string
always yields the hex code with a `'#'`.

## Input formats

### Correct input HEX-strings
* `'#RRGGBB'`   - full color without alpha, where RR, GG, BB are 2-digits hexadecimal numbers, ex. '#ff9966'
* `'#RGB'`      - short color without alpha, where R, G, B are 1-digit hexadecimal numbers, ex. '#f96'
* `'#RRGGBBAA'` - full color with alpha, ex. '#ff9966cc' is equivalent of rgba(255,153,102,80%) or rgba(255,63,42,0.8)
* `'#RGBA'`     - short color with alpha, the same as above

The first character '#' can be omitted, so 'ff9966' is equivalent of '#ff9966'

### Correct RGB or RGBA strings
* `'rgb(255,153,51)'` - R-, G- and B-components
* `'rgba(255,153,51,0.8)'` - the same, with an explicit alpha value as float
* `'rgba(255,153,51,80%)'` - the same, with an explicit alpha value as percents

### Correct HSL or HSLA strings
* `'hsl(120,100%,50%)'` - H-, S- and L-components
* `'hsla(120,100%,50%,0.5)'` - the same, with an explicit alpha value

### Correct input RGB-arrays are
* `['r' => 255,    'g' => 0,    'b' => 51]`                - range 0 - 255
* `['r' => 255,    'g' => 0,    'b' => 51,    'a' => 0.5]` - the same, with an explicit alpha value as float
* `['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 0.5]` - the same color but range 0.0% - 100.0%
* `['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => '50%']` - the same, with an explicit alpha value as percent

You can use uppercase indexes, ex. `['R' => 255, 'G' => 0, 'B' => 51]`

### Also, RGB-arrays can be with missed indexes:
* `[255,    0,    51]`         - range 0 - 255
* `[255,    0,    51,    0.5]` - the same, with an explicit alpha value as float
* `['100%', '0%', '20%', 0.5]` - the same color but range 0.0% - 100.0%
* `['100%', '0%', '20%', '50%']` - the same, with an explicit alpha value as percent

### Correct input HSL-arrays are
* `['h' => 120, 's' => 1, 'l' => 0.5]`                     - 'h' is range 0 - 360, 's' and 'l' are range 0.0 - 1.0
* `['h' => 120, 's' => 1, 'l' => 0.5, 'a' => 0.3]`         - the same, with an explicit alpha value as float
* `['h' => 120, 's' => '100%', 'l' => '50%']`              - the same, with float values as percents
* `['h' => 120, 's' => '100%', 'l' => '50%', 'a' => '30%']` - the same

You can use uppercase indexes, ex. `['H' => 120, 'S' => 1, 'L' => 0.5]`

A positional array is always read as RGB, because `[120, 1, 0.5]` is a valid RGB triplet too.
To pass a positional HSL array use `setHsl([120, 1, 0.5])`.

## Methods

### Set the whole color

* `setHex($hex)` - accepts every HEX(A) notation listed above
* `setRgb($array)` - RGB(A) array
* `setRgbStr($string)` - `'rgb(...)'` or `'rgba(...)'` string
* `setHsl($array)` - HSL(A) array

### Set color components

* `setRed($value)`
* `setGreen($value)`
* `setBlue($value)`
* `setAlpha($value)`
* `setHue($value)`
* `setSaturation($value)`
* `setLightness($value)`

Values accept plain numbers, percent strings (`'50%'`) and, for the RGB channels, the `'#ff'`
notation. The same channels are available as magic properties, with short aliases:

```php
$color = new AceColors('#3388cc');

echo $color->red;        // 51
echo $color->r;          // the same
echo $color->luma;       // 0.2267...

$color->green = 0;
$color->h     = 120;
```

### Get color in different formats

* `getHex()`, `getHexa()`
* `getRgb()`, `getRgba()`
* `getRgbStr()`, `getRgbaStr()`
* `getHsl()`, `getHsla()`
* `getHslStr()`, `getHslaStr()`

### Static converters

* `hexToRgb()`, `hexToRgba()`, `hexToHsl()`
* `rgbToHex()`, `rgbaToHex()`, `rgbaToHexa()`, `rgbToHsl()`, `rgbToStr()`, `rgbaToStr()`
* `hslToHex()`, `hslaToHex()`, `hslaToHexa()`, `hslToRgb()`, `hslToRgba()`, `hslaToRgba()`, `hslToStr()`, `hslaToStr()`

A converter whose result name has no `a` drops the alpha channel: `hexToRgb()` returns
`['r', 'g', 'b']`, while `hexToRgba()` also returns `'a'`. When the input carries no alpha,
the static converters return `'a' => null`, whereas `getRgba()` on an instance returns `1.0`.

### Manipulations

These modify the color in place and return `$this`, so they can be chained:

* `lighten($amount = 10)` - lighten by the given percentage, `lighten(0)` goes halfway to white
* `darken($amount = 10)` - darken by the given percentage, `darken(0)` goes halfway to black
* `saturate($amount = 10)` - increase saturation, a negative amount decreases it
* `desaturate($amount = 10)` - decrease saturation
* `invert()` - invert the color
* `complementary()` - rotate the hue by 180 degrees
* `mix($color, $amount = 0)` - mix with another color or `AceColors` instance, the amount ranges -100..0..+100

The amounts accept percents (`50`, `'50%'`) and fractions (`0.5`) alike.

### Methods returning a new object

The originals are left untouched:

* `cloneColor()`
* `makeLighter($amount = 10)`, `makeDarker($amount = 10)`
* `makeInverted()`, `makeComplimentary()`

### Other

* `luma()` - relative luminance, 0.0 for black and 1.0 for white ([WCAG 2.0](https://www.w3.org/TR/2008/REC-WCAG20-20081211/#relativeluminancedef))
* `isLight()`, `isDark()` - whether the color is considered light or dark
* `getGradientArray($amount = 10)` - a `['light' => ..., 'dark' => ...]` pair of hex codes for a gradient
* `getCssGradient($amount = 10, $vintageBrowsers = false)` - a ready CSS3 gradient
* `useSharp($bool)` - whether the hex output carries the `'#'` prefix

## Key case of the returned arrays

By default the returned arrays use lowercase keys. If your storage expects uppercase ones,
switch it globally:

```php
AceColors::setKeyCase(CASE_UPPER);

print_r(AceColors::hexToHsl('#6B513E'));    // ['H' => ..., 'S' => ..., 'L' => ...]

AceColors::setKeyCase(CASE_LOWER);          // back to the default
```

Input is accepted in either case regardless of this setting, and methods returning strings are
not affected. The setting is global for the process, so a long-running worker should restore it
itself. `getKeyCase()` returns the current value.

## Tests

```
$ composer test
```

## License

MIT
