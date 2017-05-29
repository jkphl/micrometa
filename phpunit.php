<?php

// Start the built-in web server
chdir(__DIR__);
$server = defined('HHVM_VERSION') ?
    'hhvm -m server -d hhvm.server.host=%s -d hhvm.server.type=fastcgi -d hhvm.server.port=%d -d hhvm.server.source_root=%s' :
    'php -S %s:%d -t %s';
$command = sprintf($server, WEB_SERVER_HOST, WEB_SERVER_PORT, WEB_SERVER_DOCROOT);
$process = proc_open($command, [['pipe', 'r']], $pipes);
$pstatus = proc_get_status($process);
$pid = $pstatus['pid'];
echo sprintf('%s - Web server started on %s:%d with PID %d', date('r'), WEB_SERVER_HOST, WEB_SERVER_PORT, $pid).PHP_EOL;

// Register shutdown function to stop the built-in webserver
register_shutdown_function(function () use ($pid) {
    echo sprintf('%s - Killing process with ID %d', date('r'), $pid).PHP_EOL;
    (stripos(php_uname('s'), 'win') > -1) ? exec("taskkill /F /T /PID $pid") : exec("kill -9 $pid");
});

error_reporting(E_ALL);
$autoloader = __DIR__.'/vendor/autoload.php';
if (!file_exists($autoloader)) {
    echo "Composer autoloader not found: $autoloader".PHP_EOL;
    echo "Please issue 'composer install' and try again.".PHP_EOL;
    exit(1);
}
require $autoloader;
