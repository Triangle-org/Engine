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

namespace Triangle\Engine\Console\Question;

use Triangle\Engine\Console\Exception\InvalidArgumentException;

/**
 * Represents a choice question.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ChoiceQuestion extends Question
{
    private $choices;
    private $multiselect = false;
    private $prompt = ' > ';
    private $errorMessage = 'Value "%s" is invalid';

    /**
     * @param string $question The question to ask to the user
     * @param array $choices The list of available choices
     * @param mixed $default The default answer to return
     */
    public function __construct(string $question, array $choices, $default = null)
    {
        if (!$choices) {
            throw new \LogicException('Choice question must have at least 1 choice available.');
        }

        parent::__construct($question, $default);

        $this->choices = $choices;
        $this->setValidator($this->getDefaultValidator());
        $this->setAutocompleterValues($choices);
    }

    /**
     * Returns available choices.
     *
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Sets multiselect option.
     *
     * When multiselect is set to true, multiple choices can be answered.
     *
     * @return $this
     */
    public function setMultiselect(bool $multiselect)
    {
        $this->multiselect = $multiselect;
        $this->setValidator($this->getDefaultValidator());

        return $this;
    }

    /**
     * Returns whether the choices are multiselect.
     *
     * @return bool
     */
    public function isMultiselect()
    {
        return $this->multiselect;
    }

    /**
     * Gets the prompt for choices.
     *
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * Sets the prompt for choices.
     *
     * @return $this
     */
    public function setPrompt(string $prompt)
    {
        $this->prompt = $prompt;

        return $this;
    }

    /**
     * Sets the error message for invalid values.
     *
     * The error message has a string placeholder (%s) for the invalid value.
     *
     * @return $this
     */
    public function setErrorMessage(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
        $this->setValidator($this->getDefaultValidator());

        return $this;
    }

    private function getDefaultValidator(): callable
    {
        $choices = $this->choices;
        $errorMessage = $this->errorMessage;
        $multiselect = $this->multiselect;
        $isAssoc = $this->isAssoc($choices);

        return function ($selected) use ($choices, $errorMessage, $multiselect, $isAssoc) {
            if ($multiselect) {
                // Check for a separated comma values
                if (!preg_match('/^[^,]+(?:,[^,]+)*$/', (string)$selected, $matches)) {
                    throw new InvalidArgumentException(sprintf($errorMessage, $selected));
                }

                $selectedChoices = explode(',', (string)$selected);
            } else {
                $selectedChoices = [$selected];
            }

            if ($this->isTrimmable()) {
                foreach ($selectedChoices as $k => $v) {
                    $selectedChoices[$k] = trim((string)$v);
                }
            }

            $multiselectChoices = [];
            foreach ($selectedChoices as $value) {
                $results = [];
                foreach ($choices as $key => $choice) {
                    if ($choice === $value) {
                        $results[] = $key;
                    }
                }

                if (\count($results) > 1) {
                    throw new InvalidArgumentException(sprintf('The provided answer is ambiguous. Value should be one of "%s".', implode('" or "', $results)));
                }

                $result = array_search($value, $choices);

                if (!$isAssoc) {
                    if (false !== $result) {
                        $result = $choices[$result];
                    } elseif (isset($choices[$value])) {
                        $result = $choices[$value];
                    }
                } elseif (false === $result && isset($choices[$value])) {
                    $result = $value;
                }

                if (false === $result) {
                    throw new InvalidArgumentException(sprintf($errorMessage, $value));
                }

                // For associative choices, consistently return the key as string:
                $multiselectChoices[] = $isAssoc ? (string)$result : $result;
            }

            if ($multiselect) {
                return $multiselectChoices;
            }

            return current($multiselectChoices);
        };
    }
}
