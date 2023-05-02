<?php

namespace support\console\Command;

use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;

class NginxDisableCommand extends Command
{
    protected static ?string $defaultName = 'nginx:disable';
    protected static ?string $defaultDescription = 'Удалить сайт из Nginx';

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
        if (!is_dir("/etc/nginx/sites-enabled")) {
            $output->writeln("<error>Папка /etc/nginx/sites-enabled не существует</>");
            return self::FAILURE;
        }

        $domain = config('app.domain');
        if (empty($domain)) {
            $output->writeln("<error>Не задан app.domain</>");
            return self::FAILURE;
        }
        $file = "/etc/nginx/sites-enabled/$domain.conf";

        if (is_file($file)) {
            @unlink($file);
            $output->writeln("<info>Ссылка удалена</>");

            $output->writeln("<info>Проверка конфигурации:</>");
            exec("nginx -t");

            exec("service nginx restart");
            $output->writeln("<info>Nginx перезагружен</>");
        } else {
            $output->writeln("<error>Файл не существует</>");
        }

        return self::SUCCESS;
    }
}
