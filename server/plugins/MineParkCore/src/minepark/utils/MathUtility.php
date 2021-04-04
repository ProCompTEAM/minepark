<?php
namespace minepark\utils;

class MathUtility
{
    public static function interval(int $value, int $from, int $to)
    {
        return $value >= min($from, $to) and $value <= max($from, $to);
    }
}
?>