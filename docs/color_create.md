```
// create new color
$color = new avadim\AceColors\AceColors('#ff9933');
```
You can use several different input formats.

Correct input HEX-strings:
* `'#RRGGBB'`   - full color without alpha, where RR, GG, BB are 2-digits hexadecimal numbers, ex. '#ff9966'
* `'#RGB'`      - short color without alpha, where R, G, B are 1-digit hexadecimal numbers, ex. '#f96'
* `'#RRGGBBAA'` - full color with alpha, ex. '#ff9966cc' is equivalent of rgba(255,153,102,80%) or rgba(255,63,42,0.8)
* `'#RGBA'`     - short color with alpha, the same as above

The character '#' can be omitted, so `'ff9966'` is equivalent of `'#ff9966'`

Correct input RGB-arrays:
* `['r' => 255,    'g' => 0,    'b' => 51]`                - range 0 - 255
* `['r' => 255,    'g' => 0,    'b' => 51,    'a' => 0.5]` - the same, with an explicit alpha value as float
* `['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 0.5]` - the same color but range 0.0% - 100.0%
* `['r' => '100%', 'g' => '0%', 'b' => '20%', 'a' => 50%]` - the same, with an explicit alpha value as percent

You can use uppercase indexes, ex. `['R' => 255, 'G' => 0, 'B' => 51]`

Also RGB-arrays can be with missed indexes:
* `[255,    0,    51]`         - range 0 - 255
* `[255,    0,    51,    0.5]` - the same, with an explicit alpha value as float
* `['100%', '0%', '20%', 0.5]` - the same color but range 0.0% - 100.0%
* `['100%', '0%', '20%', 50%]` - the same, with an explicit alpha value as percent

Correct input HSL-arrays:
* `['h' => 120, 's' => 1, 'l' => 0.5]`                     - 'h' is range 0 - 360, 's' and 'l' are range 0.0 - 1.0
* `['h' => 120, 's' => 1, 'l' => 0.5, 'a' => 0.3]`         - the same, with an explicit alpha value as float
* `['h' => 120, 's' => '100%', 'l' => '50%']`              - the same, with float values as percents
* `['h' => 120, s' => '100%', 'l' => '50%', 'a' => '30%']` - the same

You can use uppercase indexes, ex. `['H' => 120, 'S' => 1, 'L' => 0.5]`