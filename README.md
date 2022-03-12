WebBenchFramework useing [ReactPHP](https://github.com/reactphp)

download all files, install libraries
```sh
composer install
```

Modify file config.php, mainly put your token-generate function in it. Then run
```sh
php main.php
```

every field of record is:
 * 0: success, fail
 * 1: start time
 * 2: finish time
 * 3: duration
 * 4: http status
 * 5: response
 * 6: custom message