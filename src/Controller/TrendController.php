<?php

namespace App\Controller;

use App\Controller\TwigController;
use App\Statistics\RunsStatistics;
use App\Statistics\TrendStatistics;

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

        $stats = [
            "wp" => [
                "before" => [
                    "data" => $wordpressBefore,
                    "regression" => [
                        ...TrendStatistics::regressionAnalysis($wordpressBeforeValues),
                        "line" => TrendStatistics::getRegressionPoints(TrendStatistics::regressionAnalysis($wordpressBeforeValues)['parameters'], $wordpressBefore),
                    ],
                    "runs" => [
                        "test" => RunsStatistics::runsTest($wordpressBeforeValues), 
                        "p" => RunsStatistics::runsTestPValue(RunsStatistics::runsTest($wordpressBeforeValues))
                    ],
                ],
                "after" => [
                    "data" => $wordpressAfter,
                    "regression" => [
                        ...TrendStatistics::regressionAnalysis($wordpressAfterValues),
                        "line" => TrendStatistics::getRegressionPoints(TrendStatistics::regressionAnalysis($wordpressAfterValues)['parameters'], $wordpressAfter),
                    ],
                    "runs" => [
                        "test" => RunsStatistics::runsTest($wordpressAfterValues), 
                        "p" => RunsStatistics::runsTestPValue(RunsStatistics::runsTest($wordpressAfterValues))
                    ],
                ]
            ],
            "php" => [
                "before" => [
                    "data" => $phpBefore,
                    "regression" => [
                        ...TrendStatistics::regressionAnalysis($phpBeforeValues),
                        "line" => TrendStatistics::getRegressionPoints(TrendStatistics::regressionAnalysis($phpBeforeValues)['parameters'], $phpBefore)
                    ],
                    "runs" => [
                        "test" => RunsStatistics::runsTest($phpBeforeValues), 
                        "p" => RunsStatistics::runsTestPValue(RunsStatistics::runsTest($phpBeforeValues))
                    ],
                ],
                "after" => [
                    "data" => $phpAfter,
                    "regression" => [
                        ...TrendStatistics::regressionAnalysis($phpAfterValues),
                        "line" => TrendStatistics::getRegressionPoints(TrendStatistics::regressionAnalysis($phpAfterValues)['parameters'], $phpAfter)
                    ],
                    "runs" => [
                        "test" => RunsStatistics::runsTest($phpAfterValues), 
                        "p" => RunsStatistics::runsTestPValue(RunsStatistics::runsTest($phpAfterValues))
                    ],
                ]
            ],
            "correlation" => [
                "before" => TrendStatistics::covarianceAnalyses($wordpressBeforeValues, $phpBeforeValues),
                "after" => TrendStatistics::covarianceAnalyses($wordpressAfterValues, $phpAfterValues)
            ]
        ];

        return $this->render(template: "trend.html.twig", twigParams: [
            "stats" => $stats,
            // "wp" => json_encode($wordpressAfter),
            // "php" => json_encode($phpAfter),
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