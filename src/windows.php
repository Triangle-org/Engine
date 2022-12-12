<?php

/**
 * @package     FrameX (FX) Engine
 * @link        https://localzet.gitbook.io/framex
 * 
 * @author      Ivan Zorin (localzet) <creator@localzet.ru>
 * @copyright   Copyright (c) 2018-2022 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

require_once __DIR__ . '/vendor/autoload.php';

use process\Monitor;
use support\App;
use localzet\Core\Server;

ini_set('display_errors', 'on');

App::loadAllConfig(['route']);

$error_reporting = config('app.error_reporting', E_ALL);
if (isset($error_reporting)) {
    error_reporting($error_reporting);
}

$runtime_process_path = runtime_path() . DIRECTORY_SEPARATOR . '/windows';
if (!is_dir($runtime_process_path)) {
    mkdir($runtime_process_path);
}
$process_files = [
    __DIR__ . DIRECTORY_SEPARATOR . 'start.php'
];
foreach (config('process', []) as $process_name => $config) {
    $process_files[] = write_process_file($runtime_process_path, $process_name, '');
}

foreach (config('plugin', []) as $firm => $projects) {
    foreach ($projects as $name => $project) {
        if (!is_array($project)) {
            continue;
        }
        foreach ($project['process'] ?? [] as $process_name => $config) {
            $process_files[] = write_process_file($runtime_process_path, $process_name, "$firm.$name");
        }
    }
    foreach ($projects['process'] ?? [] as $process_name => $config) {
        $process_files[] = write_process_file($runtime_process_path, $process_name, $firm);
    }
}

function write_process_file($runtime_process_path, $process_name, $firm)
{
    $process_param = $firm ? "plugin.$firm.$process_name" : $process_name;
    $config_param = $firm ? "config('plugin.$firm.process')['$process_name']" : "config('process')['$process_name']";
    $file_content = <<<EOF
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use localzet\Core\Server;
use localzet\Framex\Config;
use support\App;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

if (is_callable('opcache_reset')) {
    opcache_reset();
}

App::loadAllConfig(['route']);
server_start('$process_param', $config_param);

if (DIRECTORY_SEPARATOR != "/") {
    Server::\$logFile = config('server')['log_file'] ?? Server::\$logFile;
}

Server::runAll();
EOF;
    $process_file = $runtime_process_path . DIRECTORY_SEPARATOR . "start_$process_param.php";
    file_put_contents($process_file, $file_content);
    return $process_file;
}

if ($monitor_config = config('process.monitor.constructor')) {
    $monitor = new Monitor(...array_values($monitor_config));
}

function popen_processes($process_files)
{
    $cmd = "php " . implode(' ', $process_files);
    $descriptorspec = [STDIN, STDOUT, STDOUT];
    $resource = proc_open($cmd, $descriptorspec, $pipes);
    if (!$resource) {
        exit("Can not execute $cmd\r\n");
    }
    return $resource;
}

$resource = popen_processes($process_files);
echo "\r\n";
while (1) {
    sleep(1);
    if (!empty($monitor) && $monitor->checkAllFilesChange()) {
        $status = proc_get_status($resource);
        $pid = $status['pid'];
        shell_exec("taskkill /F /T /PID $pid");
        proc_close($resource);
        $resource = popen_processes($process_files);
    }
}
