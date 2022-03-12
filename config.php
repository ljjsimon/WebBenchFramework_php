<?php
$config = [
    'concurrent_num' => 1,
    'log_file' => '/tmp/webbench.log', // leave empty to log nothing
    'log_response' => true
];

/**
 * return request params
 */
function getRequestParams() : array
{
    $url = 'http://www.baidu.com/';
    $method = 'get';
    $body = [];
    $header = [];

    return [
        'url' => $url,
        'method' => $method,
        'body' => $body,
        'header' => $header
    ];
}

/**
 * callback on success
 * @return will be in log
 */
function onSuccess(Psr\Http\Message\ResponseInterface $response, float $duration) : string
{
    return '';
}

/**
 * callback on error
 * @return will in log
 */
function onError(Exception $e) : string
{
    return '';
}