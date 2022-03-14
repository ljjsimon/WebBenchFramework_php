<?php
namespace WebBenchFramework;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Core
{
    public static function run($param)
    {
        $config = $param['config'];
        /**
         * records is a array of records
         * every field of record is:
         * 0: success, fail
         * 1: start time
         * 2: finish time
         * 3: duration
         * 4: http status
         * 5: response
         * 6: custom message
         */
        $records = [];
        $promises = [];

        $client = new Client([
            'timeout' => $config['timeout']
        ]);

        for ($i = 0; $i < $config['concurrency']; $i++) {
            $request_param = $param['getRequestParams']($i);
            $options = $request_param['options'];
            /*
            if($options['multipart']){
                foreach($options['multipart'] as &$file) {
                    if(!empty($file['path'])){
                        $file['contents'] = Psr7\Utils::tryFopen($file['path'], 'r');
                    }
                }
            }
            */

            $start = microtime(true);
            $promises[] = $client->requestAsync($request_param['method'], $request_param['url'], $options)->then(
                function (Response $response) use (&$param, &$records, $i, $start){
                    // this is delivered each successful response
                    $fin = microtime(true);
                    $record = [
                        'success',
                        $start,
                        $fin,
                        $fin - $start,
                        $response->getStatusCode()
                    ];
                    if($param['config']['debug']){
                        echo $response->getBody();
                    }
                    if($param['config']['log_response_body']){
                        $record[] = $response->getBody();
                    }
                    if(is_callable($param['onSuccess'])){
                        try{
                            $record[] = $param['onSuccess']($i, $response, $fin - $start);
                        } catch (\Exception $e){
                            //
                        }
                    }
                    $records[] = $record;
                },
                function (Exception $e) use (&$param, &$records, $i, $start) {
                    // this is delivered each failed request
                    $fin = microtime(true);
                    $record = [
                        'fail',
                        $start,
                        $fin,
                        $fin - $start
                    ];

                    if(is_callable($param['onError'])){
                        try{
                            $record[] = $param['onError']($i, $e);
                        } catch (\Exception $e1){
                            //
                        }
                    }
                    $records[] = $record;
                    /*
                    if($e instanceof \GuzzleHttp\Exception\RequestException){
                        //
                    } else if($e instanceof \GuzzleHttp\Exception\ConnectException){
                        //
                    }
                    */
                }
            );
        }
        $responses = Promise\Utils::settle($promises)->wait();
        $success_count = 0;
        $fail_count = 0;
        $all_duration = 0;
        $min_duration = INF;
        $max_duration = 0;
        $duration_arr = [];
        foreach($records as $record){
            if($record[0] == 'fail'){
                $fail_count++;
            } else {
                $success_count++;
                $duration = $record[3];
                $all_duration += $duration;
                $min_duration = min($min_duration, $duration);
                $max_duration = max($duration, $max_duration);
                $duration_arr[] = $duration;
            }

            if($config['log_file']) {
                file_put_contents($config['log_file'], implode("\t", $record)."\n", FILE_APPEND);
            }
        }

        echo 'concurrency: ', $config['concurrency'], "\n";
        echo 'success: ', $success_count, ', percent: ', sprintf("%.2f", ($success_count / count($records)) * 100), "%\n";
        echo 'fail: ', $fail_count, ', percent: ',sprintf("%.2f", ($fail_count / count($records)) * 100), "%\n";
        echo 'Requests Per Second(rps)', $success_count / $all_duration, "\n";
        echo 'average duration: ', $all_duration / $success_count, "\n";
        echo 'min duration: ', $min_duration, "\n";
        echo 'max duration: ', $max_duration, "\n";

        sort($duration_arr);
        echo 'p95: ', $duration_arr[floor(0.95 * count($duration_arr))], "\n";
        echo 'p99:', $duration_arr[floor(0.99 * count($duration_arr))], "\n";
        
    }
}