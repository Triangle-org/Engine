<?php

namespace support\console\Command;

use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;

class GitWebhookCommand extends Command
{
    protected static $defaultName = 'git:webhook|git-webhook';
    protected static $defaultDescription = 'Добавить Route для GitHub Webhook';

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

        Triangle\Engine\Route::any('/.githook', function(support\Request \$request) {
            \$output = exec('cd ' . base_path() . ' && sudo git pull');
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
