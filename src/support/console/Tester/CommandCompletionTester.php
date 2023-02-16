<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\Tester;

use support\console\Command\Command;
use support\console\Completion\CompletionInput;
use support\console\Completion\CompletionSuggestions;

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
            $options[] = '--'.$option->getName();
        }

        return array_map('strval', array_merge($options, $suggestions->getValueSuggestions()));
    }
}
