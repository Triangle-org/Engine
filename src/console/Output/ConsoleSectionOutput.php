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

namespace Triangle\Engine\Console\Output;

use Triangle\Engine\Console\Formatter\OutputFormatterInterface;
use Triangle\Engine\Console\Helper\Helper;
use Triangle\Engine\Console\Terminal;

/**
 * @author Pierre du Plessis <pdples@gmail.com>
 * @author Gabriel Ostrolucký <gabriel.ostrolucky@gmail.com>
 */
class ConsoleSectionOutput extends StreamOutput
{
    private $content = [];
    private $lines = 0;
    private $sections;
    private $terminal;

    /**
     * @param resource $stream
     * @param ConsoleSectionOutput[] $sections
     */
    public function __construct($stream, array &$sections, int $verbosity, bool $decorated, OutputFormatterInterface $formatter)
    {
        parent::__construct($stream, $verbosity, $decorated, $formatter);
        array_unshift($sections, $this);
        $this->sections = &$sections;
        $this->terminal = new Terminal();
    }

    /**
     * Clears previous output for this section.
     *
     * @param int $lines Number of lines to clear. If null, then the entire output of this section is cleared
     */
    public function clear(int $lines = null)
    {
        if (empty($this->content) || !$this->isDecorated()) {
            return;
        }

        if ($lines) {
            array_splice($this->content, -($lines * 2)); // Multiply lines by 2 to cater for each new line added between content
        } else {
            $lines = $this->lines;
            $this->content = [];
        }

        $this->lines -= $lines;

        parent::doWrite($this->popStreamContentUntilCurrentSection($lines), false);
    }

    /**
     * Overwrites the previous output with a new message.
     *
     * @param array|string $message
     */
    public function overwrite($message)
    {
        $this->clear();
        $this->writeln($message);
    }

    public function getContent(): string
    {
        return implode('', $this->content);
    }

    /**
     * @internal
     */
    public function addContent(string $input)
    {
        foreach (explode(\PHP_EOL, $input) as $lineContent) {
            $this->lines += ceil($this->getDisplayLength($lineContent) / $this->terminal->getWidth()) ?: 1;
            $this->content[] = $lineContent;
            $this->content[] = \PHP_EOL;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite(string $message, bool $newline)
    {
        if (!$this->isDecorated()) {
            parent::doWrite($message, $newline);

            return;
        }

        $erasedContent = $this->popStreamContentUntilCurrentSection();

        $this->addContent($message);

        parent::doWrite($message, true);
        parent::doWrite($erasedContent, false);
    }

    /**
     * At initial stage, cursor is at the end of stream output. This method makes cursor crawl upwards until it hits
     * current section. Then it erases content it crawled through. Optionally, it erases part of current section too.
     */
    private function popStreamContentUntilCurrentSection(int $numberOfLinesToClearFromCurrentSection = 0): string
    {
        $numberOfLinesToClear = $numberOfLinesToClearFromCurrentSection;
        $erasedContent = [];

        foreach ($this->sections as $section) {
            if ($section === $this) {
                break;
            }

            $numberOfLinesToClear += $section->lines;
            $erasedContent[] = $section->getContent();
        }

        if ($numberOfLinesToClear > 0) {
            // move cursor up n lines
            parent::doWrite(sprintf("\x1b[%dA", $numberOfLinesToClear), false);
            // erase to end of screen
            parent::doWrite("\x1b[0J", false);
        }

        return implode('', array_reverse($erasedContent));
    }

    private function getDisplayLength(string $text): int
    {
        return Helper::width(Helper::removeDecoration($this->getFormatter(), str_replace("\t", '        ', $text)));
    }
}
