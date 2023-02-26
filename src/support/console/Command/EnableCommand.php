<?php

namespace support\console\Command;

use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;
use Throwable;

class EnableCommand extends Command
{
    protected static $defaultName = 'enable';
    protected static $defaultDescription = 'Добавить проект в автозагрузку';

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

        // Парсим домен из пути до сайта
        $domain = config('app.domain', explode('://', config('server.listen'))[1]);
        $directory = base_path();

        // Собираем конфигурацию
        $conf = <<<EOF
        [program:$domain]
        user = root
        command = php master restart
        directory = $directory
        numprocs = 1
        autorestart = true
        autostart = true
        EOF;

        // Задаём путь до файла
        $file = "/etc/supervisor/conf.d/$domain.conf";

        // Создаём файл и записываем конфигурацию
        $fstream = fopen($file, 'w');
        fwrite($fstream, $conf);
        fclose($fstream);

        $output->writeln("<comment>Конфигурация создана</>");

        // Перезапускаем Supervisor
        exec("service supervisor restart");

        $output->writeln("<info>Supervisor перезапущен</>");
        return self::SUCCESS;
    }
}
