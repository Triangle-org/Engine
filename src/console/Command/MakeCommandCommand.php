<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Triangle\Engine\Console\Command;

use Triangle\Engine\Console\Input\InputArgument;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;


class MakeCommandCommand extends Command
{
    protected static ?string $defaultName = 'make:command';
    protected static ?string $defaultDescription = 'Добавить команду';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название команды');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $name = $input->getArgument('name');
        $output->writeln("Создание команды $name");
        if (!($pos = strrpos($name, '/'))) {
            $name = $this->getClassName($name);
            $file = "app/command/$name.php";
            $namespace = 'app\command';
        } else {
            $path = 'app/' . substr($name, 0, $pos) . '/command';
            $name = $this->getClassName(substr($name, $pos + 1));
            $file = "$path/$name.php";
            $namespace = str_replace('/', '\\', $path);
        }
        $this->createCommand($name, $namespace, $file, $command);

        return self::SUCCESS;
    }

    protected function getClassName($name): string
    {
        return preg_replace_callback('/:([a-zA-Z])/', function ($matches) {
                return strtoupper($matches[1]);
            }, ucfirst($name)) . 'Command';
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     * @param $command
     * @return void
     */
    protected function createCommand($name, $namespace, $file, $command): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $desc = str_replace(':', ' ', $command);
        $command_content = <<<EOF
<?php

namespace $namespace;

use Triangle\Engine\Console\Command\Command;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Input\InputOption;
use Triangle\Engine\Console\Input\InputArgument;
use Triangle\Engine\Console\Output\OutputInterface;


class $name extends Command
{
    protected static \$defaultName = '$command';
    protected static \$defaultDescription = '$desc';

    /**
     * @return void
     */
    protected function configure()
    {
        \$this->addArgument('name', InputArgument::OPTIONAL, 'Описание');
    }

    /**
     * @param InputInterface \$input
     * @param OutputInterface \$output
     * @return int
     */
    protected function execute(InputInterface \$input, OutputInterface \$output)
    {
        \$name = \$input->getArgument('name');
        \$output->writeln('Выполнена команда $command');
        return self::SUCCESS;
    }

}

EOF;
        file_put_contents($file, $command_content);
    }
}
