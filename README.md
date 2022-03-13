WebBenchFramework useing [Guzzle](https://github.com/guzzle/guzzle)

download all files, install libraries
```sh
composer install
```

Modify file main.php, mainly put your token-generate function in $getRequestParams. Then run
```sh
php main.php
```

see https://docs.guzzlephp.org/en/stable/request-options.html#form-params for request_params

every field of log record is:
 * 0: success, fail
 * 1: start time
 * 2: finish time
 * 3: duration
 * 4: http status
 * 5: response
 * 6: custom message