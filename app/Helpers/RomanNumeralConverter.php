<?php

namespace App\Helpers;

class RomanNumeralConverter
{
    /**
     * Converts an integer to a Roman numeral.
     *
     * @param int $number The integer to convert.
     * @param bool $lowercase If true, returns lowercase Roman numerals.
     * @return string
     */
    public static function convertToRoman(int $number, bool $lowercase = false): string
    {
        $map = array(
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1
        );

        $roman = '';
        while ($number > 0) {
            foreach ($map as $key => $value) {
                if ($number >= $value) {
                    $roman .= $key;
                    $number -= $value;
                    break;
                }
            }
        }

        return $lowercase ? strtolower($roman) : $roman;
    }
}
