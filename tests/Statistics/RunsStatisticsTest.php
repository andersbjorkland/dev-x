<?php

use App\Statistics\RunsStatistics;
use App\Statistics\Statistics;
use PHPUnit\Framework\TestCase;

final class RunsStatisticsTest extends TestCase
{
    private array $randomSample1 = [11, 8, 7, 12, 11, 6, 12, 9, 13, 12, 14, 7, 6, 9, 11, 12, 15, 10, 8, 7];
    private array $randomSample2 = [10, 8, 7, 11, 8, 7, 12, 11, 6, 12, 9, 13, 12, 14, 7, 6, 9, 11, 12, 15 ];


    public function testRandomSampleMean(): void
    {
        $this->assertEquals(
            11,
            RunsStatistics::runsMean($this->randomSample1)
        );
    }

    public function testRandomSampleStdDev(): void
    {
        $this->assertEquals(
            2.18,
            round(RunsStatistics::runsStdDev($this->randomSample1),2)
        );
    }

    public function testCountRuns(): void
    {
        $this->assertEquals(
            10,
            RunsStatistics::countRuns($this->randomSample2)
        );
    }

    public function testRunsTest(): void
    {
        $this->assertEquals(
            -0.459,
            round(RunsStatistics::runsTest($this->randomSample1), 3)
        );
    }

    public function testRunsTestPValue(): void
    {
        $this->assertEquals(
            0.646,
            round(RunsStatistics::runsTestPValue(RunsStatistics::runsTest($this->randomSample1)), 3)
        );
    }
}