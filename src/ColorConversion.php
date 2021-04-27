<?php

namespace PHPHue;

trait ColorConversion
{
    /**
     * @param $rgb
     * @return float[]|int[]
     */
    public function xy_from_rgb($rgb)
    {

        $rgb['red'] = $this->colour_to_point_value($rgb['red']);
        $rgb['green'] = $this->colour_to_point_value($rgb['green']);
        $rgb['blue'] = $this->colour_to_point_value($rgb['blue']);

        $x = $rgb['red'] * 0.4360747 + $rgb['green'] * 0.3850649 + $rgb['blue'] * 0.0930804;
        $y = $rgb['red'] * 0.2225045 + $rgb['green'] * 0.7168786 + $rgb['blue'] * 0.0406169;
        $z = $rgb['red'] * 0.0139322 + $rgb['green'] * 0.0971045 + $rgb['blue'] * 0.7141733;

        if (0 == ($x + $y + $z)) {
            $cx = $cy = 0;
        } else {
            $cx = $x / ($x + $y + $z);
            $cy = $y / ($x + $y + $z);
        }

        return array($cx, $cy);
    }

    /**
     * @param $hex
     * @return array
     */
    public function rgb_from_hex($hex)
    {
        $hex = ltrim($hex, '#');

        list($rgb['red'], $rgb['green'], $rgb['blue']) = str_split($hex, 2);

        $rgb = array_map('hexdec', $rgb);

        return $rgb;
    }

    /**
     * @param $colour
     * @return float|int
     */
    public function colour_to_point_value($colour)
    {
        $colour = $colour < 0   ? 0   : $colour;
        $colour = $colour > 255 ? 255 : $colour;
        return ($colour > 0.04045) ? pow(($colour + 1.055), 2.4) : ($colour / 12.92);
    }

    public function xy_from_hex($hex)
    {
        $rgb = $this->rgb_from_hex($hex);
        return $this->xy_from_rgb($rgb);
    }
}
