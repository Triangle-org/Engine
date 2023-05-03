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

namespace Triangle\Engine\Console\Helper;

/**
 * Helps outputting debug information when running an external program from a command.
 *
 * An external program can be a Process, an HTTP request, or anything else.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DebugFormatterHelper extends Helper
{
    private const COLORS = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'default'];
    private $started = [];
    private $count = -1;

    /**
     * Starts a debug formatting session.
     *
     * @return string
     */
    public function start(string $id, string $message, string $prefix = 'RUN')
    {
        $this->started[$id] = ['border' => ++$this->count % \count(self::COLORS)];

        return sprintf("%s<bg=blue;fg=white> %s </> <fg=blue>%s</>\n", $this->getBorder($id), $prefix, $message);
    }

    /**
     * Adds progress to a formatting session.
     *
     * @return string
     */
    public function progress(string $id, string $buffer, bool $error = false, string $prefix = 'OUT', string $errorPrefix = 'ERR')
    {
        $message = '';

        if ($error) {
            if (isset($this->started[$id]['out'])) {
                $message .= "\n";
                unset($this->started[$id]['out']);
            }
            if (!isset($this->started[$id]['err'])) {
                $message .= sprintf('%s<bg=red;fg=white> %s </> ', $this->getBorder($id), $errorPrefix);
                $this->started[$id]['err'] = true;
            }

            $message .= str_replace("\n", sprintf("\n%s<bg=red;fg=white> %s </> ", $this->getBorder($id), $errorPrefix), $buffer);
        } else {
            if (isset($this->started[$id]['err'])) {
                $message .= "\n";
                unset($this->started[$id]['err']);
            }
            if (!isset($this->started[$id]['out'])) {
                $message .= sprintf('%s<bg=green;fg=white> %s </> ', $this->getBorder($id), $prefix);
                $this->started[$id]['out'] = true;
            }

            $message .= str_replace("\n", sprintf("\n%s<bg=green;fg=white> %s </> ", $this->getBorder($id), $prefix), $buffer);
        }

        return $message;
    }

    /**
     * Stops a formatting session.
     *
     * @return string
     */
    public function stop(string $id, string $message, bool $successful, string $prefix = 'RES')
    {
        $trailingEOL = isset($this->started[$id]['out']) || isset($this->started[$id]['err']) ? "\n" : '';

        if ($successful) {
            return sprintf("%s%s<bg=green;fg=white> %s </> <fg=green>%s</>\n", $trailingEOL, $this->getBorder($id), $prefix, $message);
        }

        $message = sprintf("%s%s<bg=red;fg=white> %s </> <fg=red>%s</>\n", $trailingEOL, $this->getBorder($id), $prefix, $message);

        unset($this->started[$id]['out'], $this->started[$id]['err']);

        return $message;
    }

    private function getBorder(string $id): string
    {
        return sprintf('<bg=%s> </>', self::COLORS[$this->started[$id]['border']]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'debug_formatter';
    }
}
