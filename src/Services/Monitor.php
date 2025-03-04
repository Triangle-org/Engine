<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2025 Triangle Framework Team
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <support@localzet.com>
 */

namespace Triangle\Services;

use FilesystemIterator;
use localzet\Server;
use localzet\Timer;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class FileMonitor
 */
class Monitor
{
    protected array $paths;

    /**
     * @var string
     */
    public static $lockFile = __DIR__ . '/../runtime/monitor.lock';

    /**
     * Pause monitor
     */
    public static function pause(): void
    {
        file_put_contents(static::$lockFile, time());
    }

    /**
     * Resume monitor
     */
    public static function resume(): void
    {
        clearstatcache();
        if (is_file(static::$lockFile)) {
            unlink(static::$lockFile);
        }
    }

    /**
     * Whether monitor is paused
     */
    public static function isPaused(): bool
    {
        clearstatcache();
        return file_exists(static::$lockFile);
    }

    /**
     * FileMonitor constructor.
     * @param $monitorDir
     * @param $monitorExtensions
     * @param mixed[] $monitorExtensions
     */
    public function __construct($monitorDir, protected $extensions, array $options = [])
    {
        static::resume();
        $this->paths = (array)$monitorDir;
        if (!Server::getAllServers()) {
            // Если сервер не запущен
            return;
        }

        // Проверяем отключена ли exec(), без неё всё бессмысленно
        $disableFunctions = explode(',', ini_get('disable_functions'));
        if (in_array('exec', $disableFunctions, true)) {
            echo "\nМониторинг изменений файлов отключён, потому что exec() отключен в " . PHP_CONFIG_FILE_PATH . "/php.ini\n";
        } elseif ($options['enable_file_monitor'] ?? true) {
            // Монитор работает только в режиме отладки, во избежание крашей на проде
            if (config('app.debug', true)) {
                Timer::add(1, function (): void {
                    $this->checkAllFilesChange();
                });
            } else {
                echo "\nМониторинг изменений файлов отключён в режиме демона\n";
            }
        }

        $memoryLimit = $this->getMemoryLimit($options['memory_limit'] ?? null);
        if ($memoryLimit && ($options['enable_memory_monitor'] ?? true)) {
            Timer::add(60, $this->checkMemory(...), [$memoryLimit]);
        }
    }

    /**
     * @param $monitorDir
     */
    public function checkFilesChange($monitorDir): bool
    {
        static $lastMtime, $tooManyFilesCheck;
        if (!$lastMtime) {
            $lastMtime = time();
        }

        clearstatcache();
        if (!is_dir($monitorDir)) {
            if (!is_file($monitorDir)) {
                return false;
            }

            $iterator = [new SplFileInfo($monitorDir)];
        } else {
            // Рекурсивный обход каталогов
            $dirIterator = new RecursiveDirectoryIterator($monitorDir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new RecursiveIteratorIterator($dirIterator);
        }

        $count = 0;
        foreach ($iterator as $file) {
            ++$count;

            /** @var SplFileInfo $file */
            if (is_dir($file->getRealPath())) {
                continue;
            }

            // Проверка времени
            if ($lastMtime < $file->getMTime() && in_array($file->getExtension(), $this->extensions, true)) {
                $var = 0;
                exec('"' . PHP_BINARY . '" -l ' . $file, $out, $var);
                $lastMtime = $file->getMTime();

                if ($var) {
                    continue;
                }

                echo $file . " Обновлён и перезапущен\n";
                // Отправляем SIGUSR1 в мастер-процесс для перезагрузки
                $masterPid = is_file(Server::$pidFile) ? (int)file_get_contents(Server::$pidFile) : 0;
                if (is_unix() && $masterPid) {
                    posix_kill($masterPid, SIGUSR1);
                } else {
                    // Windows так не может
                    return true;
                }

                break;
            }
        }

        if (!$tooManyFilesCheck && $count > 1000) {
            echo "Монитор: Слишком много файлов ($count) в $monitorDir, что делает мониторинг файлов очень медленным\n";
            $tooManyFilesCheck = 1;
        }

        return false;
    }

    public function checkAllFilesChange(): bool
    {
        if (static::isPaused()) {
            return false;
        }

        foreach ($this->paths as $path) {
            if ($this->checkFilesChange($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $memoryLimit
     */
    public function checkMemory($memoryLimit): void
    {
        if (static::isPaused() || $memoryLimit <= 0) {
            return;
        }

        $ppid = posix_getppid();
        $childrenFile = "/proc/$ppid/task/$ppid/children";
        if (!is_file($childrenFile) || !($children = file_get_contents($childrenFile))) {
            return;
        }

        foreach (explode(' ', $children) as $pid) {
            $pid = (int)$pid;
            $statusFile = "/proc/$pid/status";
            if (!is_file($statusFile)) {
                continue;
            }

            if (!($status = file_get_contents($statusFile))) {
                continue;
            }

            $mem = 0;
            if (preg_match('/VmRSS\s*?:\s*?(\d+?)\s*?kB/', $status, $match)) {
                $mem = $match[1];
            }

            $mem = (int)($mem / 1024);
            if ($mem >= $memoryLimit) {
                posix_kill($pid, SIGINT);
            }
        }
    }

    /**
     * Получение лимита паняти
     * @return float
     */
    protected function getMemoryLimit($memoryLimit): float|int
    {
        if ($memoryLimit === 0) {
            return 0;
        }

        $usePhpIni = false;
        if (!$memoryLimit) {
            $memoryLimit = ini_get('memory_limit');
            $usePhpIni = true;
        }

        if ($memoryLimit == -1) {
            return 0;
        }

        $unit = strtolower((string)$memoryLimit[strlen((string)$memoryLimit) - 1]);
        if ($unit === 'g') {
            $memoryLimit = 1024 * (int)$memoryLimit;
        } elseif ($unit === 'm') {
            $memoryLimit = (int)$memoryLimit;
        } elseif ($unit === 'k') {
            $memoryLimit = ((int)$memoryLimit / 1024);
        } else {
            $memoryLimit = ((int)$memoryLimit / (1024 * 1024));
        }

        if ($memoryLimit < 30) {
            $memoryLimit = 30;
        }

        if ($usePhpIni) {
            return (int)(0.8 * $memoryLimit);
        }

        return $memoryLimit;
    }
}
