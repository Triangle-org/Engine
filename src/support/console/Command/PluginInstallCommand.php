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
use support\console\Input\InputArgument;
use support\console\Util;


class PluginInstallCommand extends Command
{
    protected static $defaultName = 'plugin:install';
    protected static $defaultDescription = 'Установить плагин';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название плагина (framex/plugin)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Установка плагина $name");
        $namespace = Util::nameToNamespace($name);
        $install_function = "\\{$namespace}\\Install::install";
        $plugin_const = "\\{$namespace}\\Install::FRAMEX_PLUGIN";
        if (defined($plugin_const) && is_callable($install_function)) {
            $install_function();
        }
        return self::SUCCESS;
    }
}
