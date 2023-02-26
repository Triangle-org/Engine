<?php

namespace support\console\Command;

use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;

class DisableCommand extends Command
{
    protected static $defaultName = 'disable';
    protected static $defaultDescription = 'Удалить проект из автозагрузки';

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
        if (!is_dir("/etc/supervisor/conf.d/")) {
            $output->writeln("<error>Для автозагрузки требуется Supervisor</>");
            return self::FAILURE;
        }

        $domain = config('app.domain', explode('://', config('server.listen'))[1]);
        $file = "/etc/supervisor/conf.d/$domain.conf";

        if (is_file($file)) {
            @unlink($file);
        }

        $output->writeln("<comment>Конфигурация удалена</>");

        exec("service supervisor restart");

        $output->writeln("<info>Supervisor перезапущен</>");
        return self::SUCCESS;
    }
}
