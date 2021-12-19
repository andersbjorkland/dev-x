<?php 

namespace App\Statistics;

class Statistics
{
    public static function mean(array $values):float
    {
        return array_sum($values) / count($values);
    }

    public static function stdDev(array $values):float
    {
        $mean = self::mean($values);
        $variance = array_reduce($values, function ($acc, $value) use ($mean) {
            $acc += pow($value - $mean, 2);
            return $acc;
        });
        return sqrt($variance / count($values));
    }

    public static function median(array $values):float
    {
        sort($values);
        $middle = count($values) / 2;
        return $values[floor($middle)];
    }
}