<?php
namespace minepark\utils;

class ArraysUtility
{
    public static function getStringFromArray(array $array, int $min, string $split_str = " ") : string
    {
       $val = -1;  
       $str = "";

       foreach($array as $a) {
            $val = $val + 1;

            if($val > $min) {
                $str .= $split_str.$a;
            } elseif($val == $min) {
                $str .= $a;
            }
       }

       return $str;
    }
}
?>