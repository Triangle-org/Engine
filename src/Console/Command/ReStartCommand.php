<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Triangle\Engine\Console\Command;

use support\App;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Input\InputOption;
use Triangle\Engine\Console\Output\OutputInterface;

class ReStartCommand extends Command
{
    protected static ?string $defaultName = 'restart';
    protected static ?string $defaultDescription = 'Перезапустить сервер. Используй -d для запуска в фоновом режиме. Используй -g для плавной остановки.';

    protected function configure(): void
    {
        $this
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'фоновый режим')
            ->addOption('graceful', 'g', InputOption::VALUE_NONE, 'плавная остановка');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (DIRECTORY_SEPARATOR === '/') {
            App::run();
        } else {
            ini_set('display_errors', 'on');

            if (class_exists('Dotenv\Dotenv') && file_exists(base_path() . '/.env')) {
                if (method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')) {
                    Dotenv::createUnsafeImmutable(base_path())->load();
                } else {
                    Dotenv::createMutable(base_path())->load();
                }
            }

            App::loadAllConfig(['route']);

            $errorReporting = config('app.error_reporting', E_ALL);
            if (isset($errorReporting)) {
                error_reporting($errorReporting);
            }

            $runtimeProcessPath = runtime_path() . DIRECTORY_SEPARATOR . '/windows';
            if (!is_dir($runtimeProcessPath)) {
                mkdir($runtimeProcessPath);
            }
            $processFiles = [
                __DIR__ . DIRECTORY_SEPARATOR . 'start.php'
            ];
            foreach (config('process', []) as $processName => $config) {
                $processFiles[] = write_process_file($runtimeProcessPath, $processName, '');
            }

            foreach (config('plugin', []) as $firm => $projects) {
                foreach ($projects as $name => $project) {
                    if (!is_array($project)) {
                        continue;
                    }
                    foreach ($project['process'] ?? [] as $processName => $config) {
                        $processFiles[] = write_process_file($runtimeProcessPath, $processName, "$firm.$name");
                    }
                }
                foreach ($projects['process'] ?? [] as $processName => $config) {
                    $processFiles[] = write_process_file($runtimeProcessPath, $processName, $firm);
                }
            }

            function write_process_file($runtimeProcessPath, $processName, $firm): string
            {
                $processParam = $firm ? "plugin.$firm.$processName" : $processName;
                $configParam = $firm ? "config('plugin.$firm.process')['$processName']" : "config('process')['$processName']";
                $fileContent = <<<EOF
            <?php
            
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            use localzet\Server;
            use Triangle\Engine\Config;
            use support\App;
            
            ini_set('display_errors', 'on');
            error_reporting(E_ALL);
            
            if (is_callable('opcache_reset')) {
                opcache_reset();
            }
            
            App::loadAllConfig(['route']);
            server_start('$processParam', $configParam);
            
            if (DIRECTORY_SEPARATOR != "/") {
                Server::\$logFile = config('server')['log_file'] ?? Server::\$logFile;
            }
            
            Server::runAll();
            EOF;
                $processFile = $runtimeProcessPath . DIRECTORY_SEPARATOR . "start_$processParam.php";
                file_put_contents($processFile, $fileContent);
                return $processFile;
            }

            if ($monitorConfig = config('process.monitor.constructor')) {
                $monitor = new Monitor(...array_values($monitorConfig));
            }

            function popen_processes($processFiles)
            {
                $cmd = '"' . PHP_BINARY . '" ' . implode(' ', $processFiles);
                $descriptorspec = [STDIN, STDOUT, STDOUT];
                $resource = proc_open($cmd, $descriptorspec, $pipes, null, null, ['bypass_shell' => true]);
                if (!$resource) {
                    exit("Can not execute $cmd\r\n");
                }
                return $resource;
            }

            $resource = popen_processes($processFiles);
            echo "\r\n";
            while (1) {
                sleep(1);
                if (!empty($monitor) && $monitor->checkAllFilesChange()) {
                    $status = proc_get_status($resource);
                    $pid = $status['pid'];
                    shell_exec("taskkill /F /T /PID $pid");
                    proc_close($resource);
                    $resource = popen_processes($processFiles);
                }
            }
        }

        return self::SUCCESS;
    }
}
