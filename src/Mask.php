<?php

namespace AP\Clues;

use RuntimeException;

class Mask
{
    private array $mask;

    public function __construct(string $mask_string)
    {
        $mask_string = trim($mask_string);
        $result      = [];
        $size_x      = null;

        foreach (explode("\n", $mask_string) as $line_number => $line) {
            $line_array = mb_str_split(trim($line));
            $positions  = [];
            foreach ($line_array as $symbol_number => $v) {
                $line_array[$symbol_number] = $v == '_' ? 0 : 1;
                if ($line_array[$symbol_number] == 1) {
                    $positions[$symbol_number] = $symbol_number;
                }
            }
            if (count($positions)) {
                $result[$line_number] = $positions;
            }

            // validate data
            if (is_null($size_x)) {
                $size_x = count($line_array);
            } else {
                if (count($line_array) != $size_x) {
                    throw new RuntimeException('invalid mask, all lines must have same length');
                }
            }
        }

        $this->mask = self::cutMask($result);
    }

    public function get(): array
    {
        return $this->mask;
    }

    public function is(int $x, int $y): bool
    {
        return isset($this->mask[$x][$y]);
    }

    private static function cutMask(array $mask): array
    {
        if (!count($mask)) {
            return $mask;
        }

        reset($mask);
        $first_key = key($mask);

        // cut by y
        if ($first_key > 0) {
            $new = [];
            foreach ($mask as $k => $v) {
                $new[$k - $first_key] = $v;
            }
            $mask = $new;
        }

        // cut by x
        $min = null;
        foreach ($mask as $v) {
            foreach ($v as $point) {
                if (is_null($min) || $point < $min) {
                    $min = $point;
                }
            }
        }
        if ($min > 0) {
            $new = [];
            foreach ($mask as $k => $points) {
                foreach ($points as $point) {
                    $point           = $point - $min;
                    $new[$k][$point] = $point;
                }
            }
            $mask = $new;
        }

        return $mask;
    }

    /**
     * @return array [X, Y]
     */
    public function max(): array
    {
        $max_x = 0;
        $max_y = 0;
        foreach ($this->mask as $line => $points) {
            foreach ($points as $point) {
                if ($point > $max_x) {
                    $max_x = $point;
                }
                if ($line > $max_y) {
                    $max_y = $line;
                }
            }
        }
        return [$max_x, $max_y];
    }

    public function rotate180(): self
    {
        list($max_x, $max_y) = $this->max();
        $new = [];
        foreach ($this->mask as $line => $points) {
            $new_line = abs($line - $max_y);
            foreach ($points as $point) {
                $point                  = abs($point - $max_x);
                $new[$new_line][$point] = $point;
            }
        }
        $this->mask = $new;
        return $this;
    }

    public function invert(): self
    {
        $max_x = $this->max()[0];
        $new   = [];
        foreach ($this->mask as $line => $points) {
            foreach ($points as $point) {
                $point              = abs($point - $max_x);
                $new[$line][$point] = $point;
            }
        }
        $this->mask = $new;

        return $this;
    }
}