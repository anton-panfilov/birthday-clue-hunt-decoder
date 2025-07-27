<?php

namespace AP\Clues;

class Uncrypter
{
    static public function renderAllIfAllHolesHaveSymbols(Data $data, Mask $mask)
    {
        $data_max = $data->max();
        $mask_max = $mask->max();

        $max_x = $data_max[0] - $mask_max[0];
        $max_y = $data_max[1] - $mask_max[1];


        self::renderAllOptionsWithOffsets($data, $mask, 0, $max_x, 0, $max_y);

        $mask->invert();
        self::renderAllOptionsWithOffsets($data, $mask, 0, $max_x, 0, $max_y);

        $mask->rotate180();
        self::renderAllOptionsWithOffsets($data, $mask, 0, $max_x, 0, $max_y);

        $mask->invert();
        self::renderAllOptionsWithOffsets($data, $mask, 0, $max_x, 0, $max_y);
    }

    static public function renderAllOptionsWithOffsets(
        Data $data,
        Mask $mask,
             $min_x,
             $max_x,
             $min_y,
             $max_y,
    )
    {
        for ($x = $min_x; $x <= $max_x; $x++) {
            for ($y = $min_y; $y <= $max_y; $y++) {
                echo "\n\n-------------------------------------\n";
                echo $data->applyMask($mask, $x, $y);
            }
        }
    }
}