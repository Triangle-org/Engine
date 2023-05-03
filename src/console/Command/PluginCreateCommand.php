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

use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Input\InputOption;
use Triangle\Engine\Console\Output\OutputInterface;
use Triangle\Engine\Console\Util;

class PluginCreateCommand extends Command
{
    protected static ?string $defaultName = 'plugin:create';
    protected static ?string $defaultDescription = 'Создать плагин';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption('name', 'name', InputOption::VALUE_REQUIRED, 'Название плагина (framex/plugin)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = strtolower($input->getOption('name'));
        $output->writeln("Создание плагина $name");
        if (!strpos($name, '/')) {
            $output->writeln('<error>Некорректное название, оно должно содержать символ \'/\' , например framex/plugin</error>');
            return self::FAILURE;
        }

        $namespace = Util::nameToNamespace($name);

        // Create dir config/plugin/$name
        if (is_dir($plugin_config_path = config_path() . "/plugin/$name")) {
            $output->writeln("<error>Папка $plugin_config_path уже существует</error>");
            return self::FAILURE;
        }

        if (is_dir($plugin_path = base_path() . "/vendor/$name")) {
            $output->writeln("<error>Папка $plugin_path уже существует</error>");
            return self::FAILURE;
        }

        // Add psr-4
        if ($err = $this->addAutoloadToComposerJson($name, $namespace)) {
            $output->writeln("<error>$err</error>");
            return self::FAILURE;
        }

        $this->createConfigFiles($plugin_config_path);

        $this->createVendorFiles($name, $namespace, $plugin_path, $output);

        return self::SUCCESS;
    }

    protected function addAutoloadToComposerJson($name, $namespace)
    {
        if (!is_file($composer_json_file = base_path() . "/composer.json")) {
            return "$composer_json_file не существует";
        }
        $composer_json = json_decode($composer_json_str = file_get_contents($composer_json_file), true);
        if (!$composer_json) {
            return "Некорректный $composer_json_file";
        }
        if (isset($composer_json['autoload']['psr-4'][$namespace . "\\"])) {
            return;
        }
        $namespace = str_replace("\\", "\\\\", $namespace);
        $composer_json_str = str_replace('"psr-4": {', '"psr-4": {' . "\n      \"$namespace\\\\\" : \"vendor/$name/src\",", $composer_json_str);
        file_put_contents($composer_json_file, $composer_json_str);
    }

    protected function createConfigFiles($plugin_config_path): void
    {
        mkdir($plugin_config_path, 0777, true);
        $app_str = <<<EOF
<?php
return [
    'enable' => true,
];
EOF;
        file_put_contents("$plugin_config_path/app.php", $app_str);
    }

    protected function createVendorFiles($name, $namespace, $plugin_path, $output): void
    {
        mkdir("$plugin_path/src", 0777, true);
        $this->createComposerJson($name, $namespace, $plugin_path);
        if (is_callable('exec')) {
            exec("composer dumpautoload");
        } else {
            $output->writeln("<info>Запустите команду 'composer dumpautoload'</info>");
        }
    }

    /**
     * @param $name
     * @param $namespace
     * @param $dest
     * @return void
     */
    protected function createComposerJson($name, $namespace, $dest): void
    {
        $namespace = str_replace('\\', '\\\\', $namespace);
        $composer_json_content = <<<EOT
{
  "name": "$name",
  "type": "library",
  "license": "MIT",
  "description": "FrameX plugin $name",
  "require": {
  },
  "autoload": {
    "psr-4": {
      "$namespace\\\\": "src"
    }
  }
}
EOT;
        file_put_contents("$dest/composer.json", $composer_json_content);
    }

    /**
     * @param $namespace
     * @param $path_relations
     * @param $dest_dir
     * @return void
     */
    protected function writeInstallFile($namespace, $path_relations, $dest_dir): void
    {
        if (!is_dir($dest_dir)) {
            mkdir($dest_dir, 0777, true);
        }
        $relations = [];
        foreach ($path_relations as $relation) {
            $relations[$relation] = $relation;
        }
        $relations = var_export($relations, true);
        $install_php_content = <<<EOT
<?php
namespace $namespace;

class Install
{
    const FRAMEX_PLUGIN = true;

    /**
     * @var array
     */
    protected static \$pathRelation = $relations;

    /**
     * @return void
     */
    public static function install()
    {
        static::installByRelation();
    }

    /**
     * @return void
     */
    public static function uninstall()
    {
        self::uninstallByRelation();
    }

    /**
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::\$pathRelation as \$source => \$dest) {
            if (\$pos = strrpos(\$dest, '/')) {
                \$parent_dir = base_path().'/'.substr(\$dest, 0, \$pos);
                if (!is_dir(\$parent_dir)) {
                    mkdir(\$parent_dir, 0777, true);
                }
            }
            //symlink(__DIR__ . "/\$source", base_path()."/\$dest");
            copy_dir(__DIR__ . "/\$source", base_path()."/\$dest");
        }
    }

    /**
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::\$pathRelation as \$source => \$dest) {
            /*if (is_link(base_path()."/\$dest")) {
                unlink(base_path()."/\$dest");
            }*/
            remove_dir(base_path()."/\$dest");
        }
    }
}
EOT;
        file_put_contents("$dest_dir/Install.php", $install_php_content);
    }
}
