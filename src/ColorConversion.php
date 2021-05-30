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

    function xyBriToRgb($x,$y,$bri)
    {
        $z = 1.0 - $x - $y;
        $Y = $bri / 255.0;
        $X = ($Y / $y) * $x;
        $Z = ($Y / $y) * $z;

        $r = $X * 1.612 - $Y * 0.203 - $Z * 0.302;
        $g = ($X * -1) * 0.509 + $Y * 1.412 + $Z * 0.066;
        $b = $X * 0.026 - $Y * 0.072 + $Z * 0.962;

        $r = $r <= 0.0031308 ? 12.92 * $r : (1.0 + 0.055) * pow($r, (1.0 / 2.4)) - 0.055;
        $g = $g <= 0.0031308 ? 12.92 * $g : (1.0 + 0.055) * pow($g, (1.0 / 2.4)) - 0.055;
        $b = $b <= 0.0031308 ? 12.92 * $b : (1.0 + 0.055) * pow($b, (1.0 / 2.4)) - 0.055;

        $maxValue = max( $r , $g, $b );

        $r = $r / $maxValue;
        $g = $g / $maxValue;
        $b = $b / $maxValue;

        $r = $r * 255; if ($r < 0) $r = 255;
        $g = $g * 255; if ($g < 0) $g = 255;
        $b = $b * 255; if ($b < 0) $b = 255;

        $r = dechex(round($r));
        $g = dechex(round($g));
        $b = dechex(round($b));

        if (strlen($r) < 2)     $r = "0" + $r;
        if (strlen($g) < 2)     $g = "0" + $g;
        if (strlen($b) < 2)     $b = "0" + $b;

        return "#".$r.$g.$b;
    }

}
