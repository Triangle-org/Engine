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


class InstallCommand extends Command
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Запуск устанощика FrameX';

    /**
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Выполнить установку Framex");
        $install_function = "\\Triangle\\Engine\\Install::install";
        if (is_callable($install_function)) {
            $install_function();
            return self::SUCCESS;
        }
        $output->writeln('<error>Эта команда требует localzet/framex версии >= 1.0.3</error>');
        return self::FAILURE;
    }
}
