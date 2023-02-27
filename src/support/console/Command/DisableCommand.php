<?php

namespace support\console\Command;

use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;

class DisableCommand extends Command
{
    protected static $defaultName = 'supervisor:disable|disable';
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
        if (!is_dir("/etc/supervisor/conf.d")) {
            $output->writeln("<error>Для автозагрузки требуется Supervisor</>");
            return self::FAILURE;
        }

        $domain = config('app.domain');
        if (empty($domain)) {
            $output->writeln("<error>Не задан app.domain</>");
            return self::FAILURE;
        }
        $file = "/etc/supervisor/conf.d/$domain.conf";
        
        if (is_file($file)) {
            @unlink($file);
            $output->writeln("<info>Ссылка удалена</>");

            exec("service supervisor restart");
            $output->writeln("<info>Supervisor перезапущен</>");
        } else {
            $output->writeln("<error>Файл не существует</>");
        }



        return self::SUCCESS;
    }
}