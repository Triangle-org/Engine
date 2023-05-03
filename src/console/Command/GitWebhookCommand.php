<?php

namespace Triangle\Engine\Console\Command;

use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;

class GitWebhookCommand extends Command
{
    protected static ?string $defaultName = 'git:webhook|git-webhook';
    protected static ?string $defaultDescription = 'Добавить Route для GitHub Webhook';

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
        $file = base_path() . "/config/route.php";
        $conf = <<<EOF

        Route::any('/.githook', function(\$request) {
            \$output = exec('cd ' . base_path() . ' && sudo git pull');
            exec('cd ' . base_path() . ' && sudo php master restart');
            return responseJson(\$output);
        });
        EOF;

        $fstream = fopen($file, 'a');
        fwrite($fstream, $conf);
        fclose($fstream);

        $output->writeln("<info>Route добавлен. Настройте репозиторий на отправку Webhook на " . config('app.domain', '{домен}') . "/.githook</>");
        return self::SUCCESS;
    }
}
