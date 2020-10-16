# AceColors
A set of methods for converting and manipulating colors. 
You can use and converting to different formats

## Install via Composer

|$ composer require avadim/ace-claculator

All instructions to install here: https://packagist.org/packages/avadim/ace-claculator

## Sample usage

```php
// create the color
$color = new avadim\AceColors\AceColors('#afd01');

// darken color and display hex code
echo $color->darken()->getHex();

// set channel values separately
$color
    ->setRed(51)
    ->setGreen(134)
    ->setBlue(200)
    ->setAlpha(0.8);

// convert color formats
avadim\AceColors\AceColors::hexToRgb('#cfaad9');    

```
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

### Correct input RGB-arrays are
* `['r' => 255,    'g' => 0,    'b' => 51]`                - range 0 - 255
* `['r' => 255,    'g' => 0,    'b' => 51,    'a' => 0.5]` - the same, with an explicit alpha value as float
* `['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 0.5]` - the same color but range 0.0% - 100.0%
* `['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 50%]` - the same, with an explicit alpha value as percent
You can use uppercase indexes, ex. ['R' => 255, 'G' => 0, 'B' => 51]

### Also, RGB-arrays can be with missed indexes:
* `[255,    0,    51]`         - range 0 - 255
* `[255,    0,    51,    0.5]` - the same, with an explicit alpha value as float
* `['100%', '0%', '20%', 0.5]` - the same color but range 0.0% - 100.0%
* `['100%', '0%', '20%', 50%]` - the same, with an explicit alpha value as percent

### Correct input HSL-arrays are
* `['h' => 120, 's' => 1, 'l' => 0.5]`                     - 'h' is range 0 - 360, 's' and 'l' are range 0.0 - 1.0
* `['h' => 120, 's' => 1, 'l' => 0.5, 'a' => 0.3]`         - the same, with an explicit alpha value as float
* `['h' => 120, 's' => '100%', 'l' => '50%']`              - the same, with float values as percents
* `['h' => 120, s' => '100%', 'l' => '50%', 'a' => '30%']` - the same
You can use uppercase indexes, ex. ['H' => 120, 'S' => 1, 'L' => 0.5]

## Methods

### Set color components

* setRed($value)
* setGreen($value)
* setBlue($value)
* setAlpha($value)
* setHue($value)
* setSaturation($value)
* setLightness($value)

### Get color in different formats

* getHex() - '001122'
* getHexa() - '001122aa'
* getRgb() - RGB-array like ['r' => 255, 'g' => 0, 'b' => 51]
* getRgba() - RGBA-array like ['h' => 120, 's' => 1, 'l' => 0.5, 'a' => 0.3]
* getRgbStr() - 'rgb(...)'
* getRgbaStr() - 'rgba(...)'
* getHsl() - ['h' => 120, 's' => 1, 'l' => 0.5]
* getHsla() - ['h' => 120, 's' => 1, 'l' => 0.5, 'a' => 0.3]
* getHslStr() - 'hsl(...)'
* getHslaStr() - 'hsla(...)'

### Manipulations

* lighten() - lighten color
* darken() - darken color
* saturate() - saturate color
* desaturate() - desaturate color
* invert() - invert color
* getGradientArray() - returns two colors for gradient
