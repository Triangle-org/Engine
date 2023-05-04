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

namespace Triangle\Engine\Console\Tester;

use Triangle\Engine\Console\Command\Command;
use Triangle\Engine\Console\Completion\CompletionInput;
use Triangle\Engine\Console\Completion\CompletionSuggestions;

/**
 * Eases the testing of command completion.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
class CommandCompletionTester
{
    private $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Create completion suggestions from input tokens.
     */
    public function complete(array $input): array
    {
        $currentIndex = \count($input);
        if ('' === end($input)) {
            array_pop($input);
        }
        array_unshift($input, $this->command->getName());

        $completionInput = CompletionInput::fromTokens($input, $currentIndex);
        $completionInput->bind($this->command->getDefinition());
        $suggestions = new CompletionSuggestions();

        $this->command->complete($completionInput, $suggestions);

        $options = [];
        foreach ($suggestions->getOptionSuggestions() as $option) {
            $options[] = '--' . $option->getName();
        }

        return array_map('strval', array_merge($options, $suggestions->getValueSuggestions()));
    }
}
