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

namespace Triangle\Engine\Console\CI;

use Triangle\Engine\Console\Output\OutputInterface;

/**
 * Utility class for Github actions.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class GithubActionReporter
{
    private $output;

    /**
     * @see https://github.com/actions/toolkit/blob/5e5e1b7aacba68a53836a34db4a288c3c1c1585b/packages/core/src/command.ts#L80-L85
     */
    private const ESCAPED_DATA = [
        '%' => '%25',
        "\r" => '%0D',
        "\n" => '%0A',
    ];

    /**
     * @see https://github.com/actions/toolkit/blob/5e5e1b7aacba68a53836a34db4a288c3c1c1585b/packages/core/src/command.ts#L87-L94
     */
    private const ESCAPED_PROPERTIES = [
        '%' => '%25',
        "\r" => '%0D',
        "\n" => '%0A',
        ':' => '%3A',
        ',' => '%2C',
    ];

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public static function isGithubActionEnvironment(): bool
    {
        return false !== getenv('GITHUB_ACTIONS');
    }

    /**
     * Output an error using the Github annotations format.
     *
     * @see https://docs.github.com/en/free-pro-team@latest/actions/reference/workflow-commands-for-github-actions#setting-an-error-message
     */
    public function error(string $message, string $file = null, int $line = null, int $col = null): void
    {
        $this->log('error', $message, $file, $line, $col);
    }

    /**
     * Output a warning using the Github annotations format.
     *
     * @see https://docs.github.com/en/free-pro-team@latest/actions/reference/workflow-commands-for-github-actions#setting-a-warning-message
     */
    public function warning(string $message, string $file = null, int $line = null, int $col = null): void
    {
        $this->log('warning', $message, $file, $line, $col);
    }

    /**
     * Output a debug log using the Github annotations format.
     *
     * @see https://docs.github.com/en/free-pro-team@latest/actions/reference/workflow-commands-for-github-actions#setting-a-debug-message
     */
    public function debug(string $message, string $file = null, int $line = null, int $col = null): void
    {
        $this->log('debug', $message, $file, $line, $col);
    }

    private function log(string $type, string $message, string $file = null, int $line = null, int $col = null): void
    {
        // Some values must be encoded.
        $message = strtr($message, self::ESCAPED_DATA);

        if (!$file) {
            // No file provided, output the message solely:
            $this->output->writeln(sprintf('::%s::%s', $type, $message));

            return;
        }

        $this->output->writeln(sprintf('::%s file=%s,line=%s,col=%s::%s', $type, strtr($file, self::ESCAPED_PROPERTIES), strtr($line ?? 1, self::ESCAPED_PROPERTIES), strtr($col ?? 0, self::ESCAPED_PROPERTIES), $message));
    }
}
