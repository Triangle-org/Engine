<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\Command;

use support\console\Command\Command;
use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;
use support\console\Helper\Table;
use localzet\FrameX\Route;

class RouteListCommand extends Command
{
    protected static $defaultName = 'route:list';
    protected static $defaultDescription = 'Список маршрутов';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Route::load(config_path());
        $headers = ['uri', 'method', 'callback', 'middleware'];
        $rows = [];
        foreach (Route::getRoutes() as $route) {
            foreach ($route->getMethods() as $method) {
                $cb = $route->getCallback();
                $cb = $cb instanceof \Closure ? 'Closure' : (is_array($cb) ? json_encode($cb) : var_export($cb, 1));
                $rows[] = [$route->getPath(), $method, $cb, json_encode($route->getMiddleware() ?: null)];
            }
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
        return self::SUCCESS;
    }
}
