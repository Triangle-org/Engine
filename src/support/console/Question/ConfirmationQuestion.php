<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\Question;

/**
 * Represents a yes/no question.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConfirmationQuestion extends Question
{
    private $trueAnswerRegex;

    /**
     * @param string $question        The question to ask to the user
     * @param bool   $default         The default answer to return, true or false
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

            $answerIsTrue = (bool) preg_match($regex, $answer);
            if (false === $default) {
                return $answer && $answerIsTrue;
            }

            return '' === $answer || $answerIsTrue;
        };
    }
}
