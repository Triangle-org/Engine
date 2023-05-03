<?php

namespace Triangle\Engine\Console\Command;

use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;

class EnableCommand extends Command
{
    protected static ?string $defaultName = 'supervisor:enable|enable';
    protected static ?string $defaultDescription = 'Добавить проект в автозагрузку';

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

        if (empty(config('app.domain'))) {
            $output->writeln("<error>Не задан app.domain</>");
            return self::FAILURE;
        }

        $domain = config('app.domain');
        $directory = base_path();
        $file = $directory . "/resources/supervisor.conf";

        if (!is_file($file)) {
            $conf = <<<EOF
            [program:$domain]
            user = root
            command = php master restart
            directory = $directory
            numprocs = 1
            autorestart = true
            autostart = true
            EOF;

            $fstream = fopen($file, 'w');
            fwrite($fstream, $conf);
            fclose($fstream);

            $output->writeln("<comment>Конфигурация создана</>");
        }

        exec("ln -sf $file /etc/supervisor/conf.d/$domain.conf");
        $output->writeln("<info>Ссылка создана</>");

        exec("service supervisor restart");
        $output->writeln("<info>Supervisor перезапущен</>");

        return self::SUCCESS;
    }
}
