<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';


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

$client = new React\Http\Browser();
for($i=0;$i<$config['concurrent_num'];$i++){
    $request_param = getRequestParams();
    
    $start = microtime(true);
    $promises[] = $client->request($request_param['method'], $request_param['url'], $request_param['header'], $request_param['body'])->then(
    
        function (Psr\Http\Message\ResponseInterface $response) use ($config, &$records, $start) {
            $fin = microtime(true);
            // any successful HTTP response will now end up here
            //var_dump($response->getStatusCode(), $response->getReasonPhrase());
            $record = [
                'success',
                $start,
                $fin,
                $fin - $start,
                $response->getStatusCode()
            ];
            if($config['log_response']){
                $record[] = json_encode($response->getBody());
            }
            try{
                $msg = onSuccess($response, $fin - $start);
                $record[] = $msg;
            } catch (Exception $e){
                //
            }
            $records[] = $record;
        },
        
        
        function (Exception $e) use (&$records, $start) {
            $fin = microtime(true);
            $record = [
                'fail',
                $start,
                $fin,
                $fin - $start
            ];

            try{
                $msg = onError($e);
                $record[] = $msg;
            } catch (Exception $e){
                //
            }
            $records[] = $record;
            /*
            if ($e instanceof React\Http\Message\ResponseException) {
                // any HTTP response error message will now end up here
                //$response = $e->getResponse();
                //var_dump($response->getStatusCode(), $response->getReasonPhrase());
            } else {
                //echo 'Error: ' . $e->getMessage() . PHP_EOL;
            }
            */
        }
    );
}

Clue\React\Block\awaitAll($promises);

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
        file_put_contents($config['log_file'], implode("\t", $record), FILE_APPEND);
    }
}

echo 'concurrency: ', $config['concurrent_num'], "\n";
echo 'success: ', $success_count, ', percent: ', sprintf("%.2f", ($success_count / count($records)) * 100), "%\n";
echo 'fail: ', $fail_count, ', percent: ',sprintf("%.2f", ($fail_count / count($records)) * 100), "%\n";
echo 'Requests Per Second(rps)', $success_count / $all_duration, "\n";
echo 'average duration: ', $all_duration / $success_count, "\n";
echo 'min duration: ', $min_duration, "\n";
echo 'max duration: ', $max_duration, "\n";

sort($duration_arr);
echo 'p95: ', $duration_arr[floor(0.95 * count($duration_arr))], "\n";
echo 'p99:', $duration_arr[floor(0.95 * count($duration_arr))], "\n";