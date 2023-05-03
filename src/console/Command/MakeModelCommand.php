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

use Throwable;
use Triangle\Engine\Console\Input\InputArgument;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;
use Triangle\Engine\Console\Util;


class MakeModelCommand extends Command
{
    protected static ?string $defaultName = 'make:model';
    protected static ?string $defaultDescription = 'Создать модель';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Название модели');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $input->getArgument('name');
        $class = Util::nameToClass($class);
        $output->writeln("Создание модели $class");
        if (!($pos = strrpos($class, '/'))) {
            $file = "app/model/$class.php";
            $namespace = 'app\model';
        } else {
            $path = 'app/' . substr($class, 0, $pos) . '/model';
            $class = ucfirst(substr($class, $pos + 1));
            $file = "$path/$class.php";
            $namespace = str_replace('/', '\\', $path);
        }
        $this->createModel($class, $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $class
     * @param $namespace
     * @param $file
     * @return void
     */
    protected function createModel($class, $namespace, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $table = Util::classToName($class);
        $table_val = 'null';
        $pk = 'id';
        try {
            if (db()->get("{$table}s")) {
                $table = "{$table}s";
            } else if (db()->get($table)) {
                $table_val = "'$table'";
                $table = "$table";
            }
            foreach (db()->orderBy('id', 'desc')->get($table) as $item) {
                if ($item->Key === 'PRI') {
                    $pk = $item->Field;
                    break;
                }
            }
        } catch (Throwable) {
        }
        $model_content = <<<EOF
<?php

namespace $namespace;

use support\Model;

class $class extends Model
{
    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected \$table = $table_val;

    /**
     * Первичный ключ, связанный с таблицей.
     *
     * @var string
     */
    protected \$primaryKey = '$pk';

    /**
     * Указывает, должна ли модель быть временной меткой.
     *
     * @var bool
     */
    public \$timestamps = false;
    
    
}

EOF;
        file_put_contents($file, $model_content);
    }
}
