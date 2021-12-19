<?php

namespace App\Controller;

use App\Controller\TwigController;
use App\Statistics\RunsStatistics;
use App\Statistics\Statistics;
use App\Statistics\TrendStatistics;
use MathPHP\Statistics\Correlation;
use React\Http\Message\Response;

class TrendController extends TwigController
{
    public function __invoke()
    {
        $path = __DIR__ . '/../../files/before21.csv';
        $data = file($path);

        $trendBefore = $this->parseCsvData($data);
        $wordpressBefore = $trendBefore['wp'];
        $phpBefore = $trendBefore['php'];

        $wordpressBeforeValues = $this->getValues($wordpressBefore);
        $phpBeforeValues = $this->getValues($phpBefore);

        $dataAfter = file(__DIR__ . '/../../files/from21.csv');
        $trendAfter = $this->parseCsvData($dataAfter);
        $wordpressAfter = $trendAfter['wp'];
        $phpAfter = $trendAfter['php'];

        $wordpressAfterValues = $this->getValues($wordpressAfter);
        $phpAfterValues = $this->getValues($phpAfter);

        $phpBeforeRegressionAnalysis = TrendStatistics::regressionAnalysis($phpBeforeValues);
        $phpRuns = RunsStatistics::runsTest($phpBeforeValues);
        $phpP = RunsStatistics::runsTestPValue($phpRuns);


        $wpBeforeRegressionAnalysis = TrendStatistics::regressionAnalysis($wordpressBeforeValues);
        $wpBeforeRuns = RunsStatistics::runsTest($wordpressBeforeValues);
        $wpBeforeP = RunsStatistics::runsTestPValue($wpBeforeRuns);
        $beforeCorrelation = TrendStatistics::covarianceAnalyses($wordpressBeforeValues, $phpBeforeValues);
        $afterCorrelation = TrendStatistics::covarianceAnalyses($wordpressAfterValues, $phpAfterValues);

        return $this->render(template: "trend.html.twig", twigParams: [
            "wp" => json_encode($wordpressAfter),
            "wpLine" => json_encode($this->getRegressionPoints($phpBeforeRegressionAnalysis, $phpBefore)),
            "wpLineFunction" => $phpBeforeRegressionAnalysis['equation'],
            "php" => json_encode($phpAfter),
            "correlation" => $beforeCorrelation,
        ]);
    }

    protected function getRegressionPoints(array $regression, array $arr):array
    {
        $points = [];
        $count = count($arr);

        for ($i=0; $i < $count; $i++) { 
            $points[] = [
                'label' => $arr[$i]['label'], 
                'y' => $regression['parameters']['b'] + $regression['parameters']['m'] * $i];
        }

        return $points;
    }
    

    protected function parseCsvData(array $arr):array
    {
        $labelIndex = 0;
        $wpIndex = 1;
        $phpIndex = 2;
        $parsedData = ['php' => [], 'wp' => []];

        for ($i = 3; $i < count($arr); $i++) {
            $row = explode(',', $arr[$i]);
            $parsedData['wp'][] = [
                'label' => $row[$labelIndex],
                'y' => intval($row[$wpIndex])
            ];
            $parsedData['php'][] = [
                'label' => $row[$labelIndex],
                'y' => intval($row[$phpIndex])
            ];
        }

        return $parsedData;
    }

    protected function getValues(array $data):array
    {
        $returnY = fn ($dataArr) => $dataArr['y'];
        $values = array_map($returnY, $data);
        return $values;
    }
}