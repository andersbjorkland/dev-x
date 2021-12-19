<?php 

namespace App\Statistics;

use MathPHP\Probability\Distribution\Continuous\StandardNormal;
use MathPHP\Statistics\Significance;

class RunsStatistics
{
    public static function observationsCount(array $values):array
    {
        $mean = Statistics::mean($values);
        $above = 0;
        $below = 0;
        foreach ($values as $value) {
            if ($value > $mean) {
                $above++;
            } else {
                $below++;
            }
        }
        return ["+" => $above, "-" => $below];
    }

    public static function runsMean(array $values):float
    {
        $runsCount = self::observationsCount($values);
        $runsMean = (2 * $runsCount["+"] * $runsCount["-"]  + count($values))/ count($values);
        return $runsMean;
    }

    public static function runsStdDev(array $values):float
    {
        $count = count($values);
        $runsCount = self::observationsCount($values);
        $a = 2 * $runsCount["+"] * $runsCount["-"];
        $b = 2 * $runsCount["+"] * $runsCount["-"] - $count;
        $c = pow($count, 2) * ($count - 1);
        $runsStdDev = sqrt(($a * $b) / $c);
        return $runsStdDev;
    }

    public static function countRuns(array $values):int
    {
        $count = 0;
        $mean = Statistics::mean($values);
        $lastObservationAboveMean = null;
        foreach($values as $observation) {
            $observationAboveMean = $observation > $mean;
            if ($observationAboveMean !== $lastObservationAboveMean) {
                $count++;
            }
            $lastObservationAboveMean = $observationAboveMean;
        }

        return $count;
    }

    public static function runsTest(array $values):float
    {
        $R = self::countRuns($values);
        $µ = self::runsMean($values);
        $σ = self::runsStdDev($values);

        $z = ($R - $µ) / $σ;

        return $z;
    }

    /**
     * Two sided probability for randomness.
     * A small p-value means that the null hypothesis is rejected. The level is up to the user.
     */
    public static function runsTestPValue(float $z):float
    {
        $standardNormal = new StandardNormal();

        $p = $standardNormal->outside(-\abs($z), \abs($z));

        return $p;
    }

    
}