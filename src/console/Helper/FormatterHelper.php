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

use Triangle\Engine\Console\Formatter\OutputFormatter;

/**
 * The Formatter class provides helpers to format messages.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FormatterHelper extends Helper
{
    /**
     * Formats a message within a section.
     *
     * @return string
     */
    public function formatSection(string $section, string $message, string $style = 'info')
    {
        return sprintf('<%s>[%s]</%s> %s', $style, $section, $style, $message);
    }

    /**
     * Formats a message as a block of text.
     *
     * @param string|array $messages The message to write in the block
     *
     * @return string
     */
    public function formatBlock($messages, string $style, bool $large = false)
    {
        if (!\is_array($messages)) {
            $messages = [$messages];
        }

        $len = 0;
        $lines = [];
        foreach ($messages as $message) {
            $message = OutputFormatter::escape($message);
            $lines[] = sprintf($large ? '  %s  ' : ' %s ', $message);
            $len = max(self::width($message) + ($large ? 4 : 2), $len);
        }

        $messages = $large ? [str_repeat(' ', $len)] : [];
        for ($i = 0; isset($lines[$i]); ++$i) {
            $messages[] = $lines[$i] . str_repeat(' ', $len - self::width($lines[$i]));
        }
        if ($large) {
            $messages[] = str_repeat(' ', $len);
        }

        for ($i = 0; isset($messages[$i]); ++$i) {
            $messages[$i] = sprintf('<%s>%s</%s>', $style, $messages[$i], $style);
        }

        return implode("\n", $messages);
    }

    /**
     * Truncates a message to the given length.
     *
     * @return string
     */
    public function truncate(string $message, int $length, string $suffix = '...')
    {
        $computedLength = $length - self::width($suffix);

        if ($computedLength > self::width($message)) {
            return $message;
        }

        return self::substr($message, 0, $length) . $suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'formatter';
    }
}
