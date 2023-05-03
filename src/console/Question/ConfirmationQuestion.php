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

/**
 * Represents a yes/no question.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConfirmationQuestion extends Question
{
    private $trueAnswerRegex;

    /**
     * @param string $question The question to ask to the user
     * @param bool $default The default answer to return, true or false
     * @param string $trueAnswerRegex A regex to match the "yes" answer
     */
    public function __construct(string $question, bool $default = true, string $trueAnswerRegex = '/^y/i')
    {
        parent::__construct($question, $default);

        $this->trueAnswerRegex = $trueAnswerRegex;
        $this->setNormalizer($this->getDefaultNormalizer());
    }

    /**
     * Returns the default answer normalizer.
     */
    private function getDefaultNormalizer(): callable
    {
        $default = $this->getDefault();
        $regex = $this->trueAnswerRegex;

        return function ($answer) use ($default, $regex) {
            if (\is_bool($answer)) {
                return $answer;
            }

            $answerIsTrue = (bool)preg_match($regex, $answer);
            if (false === $default) {
                return $answer && $answerIsTrue;
            }

            return '' === $answer || $answerIsTrue;
        };
    }
}
