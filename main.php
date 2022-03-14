<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Core.php';

use WebBenchFramework\Core;

$config = [
    'concurrency' => 1,
    'log_file' => '/tmp/webbench.log', // leave empty to log nothing
    'log_response_body' => false,
    'debug' => false, // if true, print response
    'timeout' => null, // if set, do timeout in seconds
];

/**
 * return request params
 */
$getRequestParams = function(int $index) : array
{
    $url = 'http://localhost:8088/';
    $method = 'POST';
    $data = [
        'a' => 1
    ];
    $headers = [
    ];

    return [
        'url' => $url,
        'method' => $method,
        'options' => [
            'headers' => $headers,
            //'form_params' => $data, // will add application/x-www-form-urlencoded to header
            //'json' => $data, // will add application/json to header
            'multipart' => [ // will add multipart/form-data to header
                [
                    'name' => 'a',
                    'contents' => 'b'
                ],
                [
                    'name' => 'file',
                    'contents' => fopen('/Users/linjiangjian/Downloads/C079-H04.pdf','r')
                ]
            ]
        ]
    ];
};

/**
 * callback on success
 * @return will be in log
 */
$onSuccess = function (int $index, Psr\Http\Message\ResponseInterface $response, float $duration) : string
{
    return '';
};

/**
 * callback on error
 * @return will in log
 */
$onError = function (int $index, Exception $e) : string
{
    return '';
};

Core::run([
    'config' => $config,
    'getRequestParams' => $getRequestParams,
    'onSuccess' => $onSuccess,
    'onError' => $onError
]);