<?php 

namespace App\Statistics;

use MathPHP\Statistics\Correlation;
use MathPHP\Statistics\Regression\Linear;

class TrendStatistics
{
    public static function covarianceAnalyses(array $x, array $y):array
    {
        
        $statistics = [
            'covariance' => Correlation::covariance($x, $y), 
            'pearson' => Correlation::r($x, $y),
        ];

        return $statistics;
    }

    public static function regressionAnalysis(array $simpleValues):array
    {
        $points = [];
        $length = count($simpleValues);
        for ($i = 0; $i < $length; $i++) {
            $points[] = [$i, $simpleValues[$i]];
        }

        $regression = new Linear($points);
        $parameters = $regression->getParameters();
        $equation = $regression->getEquation();

        $analysis = [
            'equation' => $equation,
            'parameters' => $parameters
        ];

        return $analysis;
    }
}