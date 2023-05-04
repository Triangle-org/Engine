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

namespace Triangle\Engine\Console\Completion;

use Triangle\Engine\Console\Input\InputOption;

/**
 * Stores all completion suggestions for the current input.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class CompletionSuggestions
{
    private $valueSuggestions = [];
    private $optionSuggestions = [];

    /**
     * Add a suggested value for an input option or argument.
     *
     * @param string|Suggestion $value
     *
     * @return $this
     */
    public function suggestValue($value): self
    {
        $this->valueSuggestions[] = !$value instanceof Suggestion ? new Suggestion($value) : $value;

        return $this;
    }

    /**
     * Add multiple suggested values at once for an input option or argument.
     *
     * @param list<string|Suggestion> $values
     *
     * @return $this
     */
    public function suggestValues(array $values): self
    {
        foreach ($values as $value) {
            $this->suggestValue($value);
        }

        return $this;
    }

    /**
     * Add a suggestion for an input option name.
     *
     * @return $this
     */
    public function suggestOption(InputOption $option): self
    {
        $this->optionSuggestions[] = $option;

        return $this;
    }

    /**
     * Add multiple suggestions for input option names at once.
     *
     * @param InputOption[] $options
     *
     * @return $this
     */
    public function suggestOptions(array $options): self
    {
        foreach ($options as $option) {
            $this->suggestOption($option);
        }

        return $this;
    }

    /**
     * @return InputOption[]
     */
    public function getOptionSuggestions(): array
    {
        return $this->optionSuggestions;
    }

    /**
     * @return Suggestion[]
     */
    public function getValueSuggestions(): array
    {
        return $this->valueSuggestions;
    }
}
