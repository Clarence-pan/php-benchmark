<?php


class Benchmark
{
    /**
     * Run a benchmark test
     * 性能测试.
     *
     * @param callable $action
     * @param array    $options
     *
     * @return array
     */
    public static function test(callable $action, $options = array())
    {
        $options = array_merge([
            'repeat' => 3, // How many times to repeat. 重复几次
            'times' => 10000, // How many times to run. 每次执行多少遍
            'warmup' => true, // Whether to warmup before test. 是否预热
            'reportOnly' => true, // Whether to return just report or with details. 仅仅返回报告
        ], $options);

        // 预热
        if ($options['warmup']) {
            call_user_func($action);
        }

        $repeat = $options['repeat'];
        $times = $options['times'];
        $result = [];

        for ($i = 0; $i < $repeat; ++$i) {
            $timeBefore = microtime(true);

            for ($j = 0; $j < $times; ++$j) {
                call_user_func($action);
            }

            $timeAfter = microtime(true);
            $timeDelta = $timeAfter - $timeBefore;
            $result[] = [
                'time' => [
                    'before' => $timeBefore,
                    'after' => $timeAfter,
                    'delta' => $timeDelta,
                    'once' => $timeDelta / $options['times'],
                ],
            ];
        }

        $getter = function ($key) {
            return function ($x) use ($key) {
                return @$x[$key];
            };
        };

        $report = [
            'aveTime' => sprintf('%.6fms', array_sum(array_map($getter('time.once'), $result)) / $options['repeat'] * 1000),
            'maxTime' => sprintf('%.6fms', max(array_map($getter('time.once'), $result)) * 1000),
            'minTime' => sprintf('%.6fms', min(array_map($getter('time.once'), $result)) * 1000),
        ];

        if ($options['reportOnly']) {
            return $report;
        }

        return [
            'report' => $report,
            'list' => $result,
        ];
    }
}