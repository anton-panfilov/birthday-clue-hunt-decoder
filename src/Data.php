<?php

namespace AP\Clues;

use RuntimeException;

class Data
{
    private array $data;

    public function __construct(string $data_string)
    {
        $this->data = [];

        $data_string = trim($data_string);
        $size_x      = null;
        $result      = [];
        foreach (explode("\n", $data_string) as $line) {
            $line_array = mb_str_split(trim($line));
            $result[]   = $line_array;

            // validate data
            if (is_null($size_x)) {
                $size_x = count($line_array);
            } else {
                if (count($line_array) != $size_x) {
                    throw new RuntimeException('invalid data, all lines must have same length');
                }
            }
        }

        $this->data = $result;
    }

    public function applyMask(Mask $mask, int $offset_x = 0, int $offset_y = 0): string
    {
        $res           = [];
        $up_to_down    = "";
        $left_to_right_index = [];
        foreach ($this->data as $line_number => $line) {
            $line_array = [];
            foreach ($line as $symbol_number => $symbol) {
                $visible      = $mask->is($line_number - $offset_y, $symbol_number - $offset_x);
                $line_array[] = $visible
                    ? $symbol : " ";
                if ($visible) {
                    $up_to_down      .= $symbol;
                    $left_to_right_index[] = [
                        $symbol, $symbol_number
                    ];
                }
            }
            $res[] = implode("   ", $line_array);
        }


        usort($left_to_right_index, function ($a, $b) {
            if ($a[1] == $b[1]) return 0;
            return $a[1] < $b[1] ? -1 : 1;
        });

        $left_to_right = "";
        foreach ($left_to_right_index as $el){
            $left_to_right.= $el[0];
        }

        $options = [
            $up_to_down,
            $left_to_right
        ];


        return implode("\n", $res) . "\n---\n" . implode("\n", $options);
    }

    /**
     * @return array [X, Y]
     */
    public function max(): array
    {
        return [
            count($this->data[0]) - 1,
            count($this->data) - 1,
        ];
    }
}