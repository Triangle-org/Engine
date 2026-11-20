<?php declare(strict_types=1);

/**
 * @package Triangle Engine
 * @link https://github.com/Triangle-org/Engine
 * @author Ivan Zorin <creator@localzet.com>
 * @copyright Copyright © 2016-2026 Localzet Group. All rights reserved.
 * @license https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class Server7HttpIntegrationTest extends TestCase
{
    public function testServer7HttpRequestUsesEngineRequestContextAndResponse(): void
    {
        if (PHP_OS_FAMILY === 'Windows'
            || !function_exists('proc_open')
            || !function_exists('posix_kill')
            || !function_exists('posix_setpgid')) {
            self::markTestSkipped('The HTTP integration fixture requires a Unix process supervisor.');
        }

        $runtimeDirectory = sys_get_temp_dir() . '/triangle-engine-http-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($runtimeDirectory, 0700));

        $process = null;

        try {
            $port = $this->reservePort();
            $logFile = $runtimeDirectory . '/server.log';
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['file', $logFile, 'a'],
                2 => ['file', $logFile, 'a'],
            ];

            $process = proc_open(
                [PHP_BINARY, __DIR__ . '/../fixtures/server7_http.php', 'start', (string)$port, $runtimeDirectory],
                $descriptors,
                $pipes,
                dirname(__DIR__, 2),
            );

            self::assertIsResource($process);
            if (isset($pipes[0]) && is_resource($pipes[0])) {
                fclose($pipes[0]);
            }

            $this->waitForServer($port, $process, $logFile);

            $socket = stream_socket_client("tcp://127.0.0.1:$port", $errorCode, $errorMessage, 2);
            self::assertIsResource($socket, "Could not connect to Server 7: $errorMessage ($errorCode)");
            stream_set_timeout($socket, 3);
            fwrite($socket, "GET /?request_id=engine-http HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n");
            $response = stream_get_contents($socket);
            fclose($socket);

            self::assertIsString($response);
            self::assertStringStartsWith('HTTP/1.1 200', $response);
            self::assertStringContainsString("\r\n\r\n", $response);

            [, $body] = explode("\r\n\r\n", $response, 2);
            self::assertSame(
                ['request_id' => 'engine-http'],
                json_decode($body, true, 512, JSON_THROW_ON_ERROR),
            );
        } finally {
            if (is_resource($process)) {
                $this->stopServer($process, $runtimeDirectory);
            }
            $this->removeRuntimeDirectory($runtimeDirectory);
        }
    }

    private function reservePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        if (!is_resource($socket)) {
            throw new RuntimeException("Could not reserve an integration port: $errorMessage ($errorCode)");
        }

        $address = stream_socket_get_name($socket, false);
        fclose($socket);

        return (int)substr(strrchr((string)$address, ':'), 1);
    }

    /** @param resource $process */
    private function waitForServer(int $port, $process, string $logFile): void
    {
        $deadline = microtime(true) + 5;
        do {
            $socket = @stream_socket_client("tcp://127.0.0.1:$port", $errorCode, $errorMessage, 0.05);
            if (is_resource($socket)) {
                fclose($socket);
                return;
            }

            $status = proc_get_status($process);
            if (!$status['running']) {
                throw new RuntimeException('Server 7 exited during startup: ' . $this->readLog($logFile));
            }

            usleep(50_000);
        } while (microtime(true) < $deadline);

        throw new RuntimeException('Server 7 did not open its HTTP listener: ' . $this->readLog($logFile));
    }

    /** @param resource $process */
    private function stopServer($process, string $runtimeDirectory): void
    {
        $status = proc_get_status($process);
        $masterPid = (int)$status['pid'];
        if ($status['running']) {
            proc_terminate($process, SIGTERM);

            $deadline = microtime(true) + 5;
            do {
                usleep(50_000);
                $status = proc_get_status($process);
            } while ($status['running'] && microtime(true) < $deadline);
        }

        $processGroup = (int)@file_get_contents($runtimeDirectory . '/process-group');
        $hasWorkers = $this->hasRunningWorker($runtimeDirectory, $masterPid);
        if ($processGroup === $masterPid && ($status['running'] || $hasWorkers)) {
            @posix_kill(-$processGroup, SIGKILL);
        } elseif ($status['running']) {
            proc_terminate($process, SIGKILL);
        }

        proc_close($process);
    }

    private function hasRunningWorker(string $runtimeDirectory, int $masterPid): bool
    {
        foreach (glob($runtimeDirectory . '/server.status.d/worker-*.json') ?: [] as $file) {
            $worker = json_decode((string)file_get_contents($file), true);
            $workerPid = (int)($worker['pid'] ?? 0);

            if (($worker['master_pid'] ?? null) === $masterPid
                && $workerPid > 1
                && @posix_kill($workerPid, 0)
                && @posix_getpgid($workerPid) === $masterPid) {
                return true;
            }
        }

        return false;
    }

    private function readLog(string $logFile): string
    {
        return is_file($logFile) ? (string)file_get_contents($logFile) : '';
    }

    private function removeRuntimeDirectory(string $directory): void
    {
        foreach (glob($directory . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($directory);
    }
}
