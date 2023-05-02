<?php

namespace support\console\Command;

use support\console\Input\InputInterface;
use support\console\Output\OutputInterface;

class GitInitCommand extends Command
{
    protected static ?string $defaultName = 'git:connect|git-connect';
    protected static ?string $defaultDescription = 'Добавить удалённый репозиторий Git';

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
        if (empty(config('app.git'))) {
            $output->writeln("<error>Не задан app.git</>");
            return self::FAILURE;
        }


        exec('git config --global --add safe.directory ' . base_path());
        exec('cd ' . base_path() . ' && sudo git init .');
        $output->writeln("<info>Git инициирован</>");

        exec('cd ' . base_path() . ' && sudo git remote add origin ' . config('app.git'));
        $output->writeln("<info>Добавлен удалённый репозиторий</>");

        exec('cd ' . base_path() . ' && sudo git fetch origin');
        $output->writeln("<info>Получены данные</>");

        $output->writeln("<info>Репозиторий связан с удалённым</>");
        return self::SUCCESS;
    }
}
