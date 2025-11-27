<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryLogger
{
    protected static $queryLog = [];
    protected static $enabled = false;
    
    /**
     * Start logging queries
     */
    public static function start()
    {
        if (static::$enabled) {
            return;
        }
        
        static::$enabled = true;
        static::$queryLog = [];
        
        DB::enableQueryLog();
        
        // Listen to query events for detailed logging
        DB::listen(function ($query) {
            $sql = $query->sql;
            $bindings = $query->bindings;
            $time = $query->time;
            
            // Replace bindings in SQL for readability
            foreach ($bindings as $binding) {
                $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            
            static::$queryLog[] = [
                'sql' => $sql,
                'time' => $time,
                'bindings' => $bindings
            ];
            
            // Log slow queries (over 100ms)
            if ($time > 100) {
                Log::warning('Slow Query Detected', [
                    'sql' => $sql,
                    'time' => $time . 'ms',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
                ]);
            }
        });
    }
    
    /**
     * Stop logging and return results
     */
    public static function stop()
    {
        if (!static::$enabled) {
            return [];
        }
        
        static::$enabled = false;
        
        $queries = static::$queryLog;
        $stats = static::getStats();
        
        // Log summary
        Log::info('Query Log Summary', $stats);
        
        return [
            'queries' => $queries,
            'stats' => $stats
        ];
    }
    
    /**
     * Get query statistics
     */
    public static function getStats()
    {
        $totalQueries = count(static::$queryLog);
        $totalTime = array_sum(array_column(static::$queryLog, 'time'));
        $slowQueries = array_filter(static::$queryLog, function ($query) {
            return $query['time'] > 100;
        });
        
        return [
            'total_queries' => $totalQueries,
            'total_time' => round($totalTime, 2) . 'ms',
            'average_time' => $totalQueries > 0 ? round($totalTime / $totalQueries, 2) . 'ms' : '0ms',
            'slow_queries' => count($slowQueries),
            'slowest_query' => $totalQueries > 0 ? max(array_column(static::$queryLog, 'time')) . 'ms' : '0ms'
        ];
    }
    
    /**
     * Get current query log
     */
    public static function getLog()
    {
        return static::$queryLog;
    }
    
    /**
     * Clear the query log
     */
    public static function clear()
    {
        static::$queryLog = [];
    }
    
    /**
     * Dump query log to browser console (for development)
     */
    public static function dump()
    {
        if (!static::$enabled) {
            return;
        }
        
        $stats = static::getStats();
        
        echo "\n<!-- Query Log -->\n";
        echo "<script>\n";
        echo "console.group('Database Query Log');\n";
        echo "console.log('Total Queries: " . $stats['total_queries'] . "');\n";
        echo "console.log('Total Time: " . $stats['total_time'] . "');\n";
        echo "console.log('Average Time: " . $stats['average_time'] . "');\n";
        echo "console.log('Slow Queries (>100ms): " . $stats['slow_queries'] . "');\n";
        echo "console.log('Slowest Query: " . $stats['slowest_query'] . "');\n";
        
        if (count(static::$queryLog) > 0) {
            echo "console.table(" . json_encode(static::$queryLog) . ");\n";
        }
        
        echo "console.groupEnd();\n";
        echo "</script>\n";
    }
}
