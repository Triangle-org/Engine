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

use PHPUnit\Framework\Assert;
use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\ConsoleOutput;
use Triangle\Engine\Console\Output\OutputInterface;
use Triangle\Engine\Console\Output\StreamOutput;
use Triangle\Engine\Console\Tester\Constraint\CommandIsSuccessful;

/**
 * @author Amrouche Hamza <hamza.simperfit@gmail.com>
 */
trait TesterTrait
{
    /** @var StreamOutput */
    private $output;
    private $inputs = [];
    private $captureStreamsIndependently = false;
    /** @var InputInterface */
    private $input;
    /** @var int */
    private $statusCode;

    /**
     * Gets the display returned by the last execution of the command or application.
     *
     * @throws \RuntimeException If it's called before the execute method
     *
     * @return string
     */
    public function getDisplay(bool $normalize = false)
    {
        if (null === $this->output) {
            throw new \RuntimeException('Output not initialized, did you execute the command before requesting the display?');
        }

        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());

        if ($normalize) {
            $display = str_replace(\PHP_EOL, "\n", $display);
        }

        return $display;
    }

    /**
     * Gets the output written to STDERR by the application.
     *
     * @param bool $normalize Whether to normalize end of lines to \n or not
     *
     * @return string
     */
    public function getErrorOutput(bool $normalize = false)
    {
        if (!$this->captureStreamsIndependently) {
            throw new \LogicException('The error output is not available when the tester is run without "capture_stderr_separately" option set.');
        }

        rewind($this->output->getErrorOutput()->getStream());

        $display = stream_get_contents($this->output->getErrorOutput()->getStream());

        if ($normalize) {
            $display = str_replace(\PHP_EOL, "\n", $display);
        }

        return $display;
    }

    /**
     * Gets the input instance used by the last execution of the command or application.
     *
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Gets the output instance used by the last execution of the command or application.
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Gets the status code returned by the last execution of the command or application.
     *
     * @throws \RuntimeException If it's called before the execute method
     *
     * @return int
     */
    public function getStatusCode()
    {
        if (null === $this->statusCode) {
            throw new \RuntimeException('Status code not initialized, did you execute the command before requesting the status code?');
        }

        return $this->statusCode;
    }

    public function assertCommandIsSuccessful(string $message = ''): void
    {
        Assert::assertThat($this->statusCode, new CommandIsSuccessful(), $message);
    }

    /**
     * Sets the user inputs.
     *
     * @param array $inputs An array of strings representing each input
     *                      passed to the command input stream
     *
     * @return $this
     */
    public function setInputs(array $inputs)
    {
        $this->inputs = $inputs;

        return $this;
    }

    /**
     * Initializes the output property.
     *
     * Available options:
     *
     *  * decorated:                 Sets the output decorated flag
     *  * verbosity:                 Sets the output verbosity flag
     *  * capture_stderr_separately: Make output of stdOut and stdErr separately available
     */
    private function initOutput(array $options)
    {
        $this->captureStreamsIndependently = \array_key_exists('capture_stderr_separately', $options) && $options['capture_stderr_separately'];
        if (!$this->captureStreamsIndependently) {
            $this->output = new StreamOutput(fopen('php://memory', 'w', false));
            if (isset($options['decorated'])) {
                $this->output->setDecorated($options['decorated']);
            }
            if (isset($options['verbosity'])) {
                $this->output->setVerbosity($options['verbosity']);
            }
        } else {
            $this->output = new ConsoleOutput(
                $options['verbosity'] ?? ConsoleOutput::VERBOSITY_NORMAL,
                $options['decorated'] ?? null
            );

            $errorOutput = new StreamOutput(fopen('php://memory', 'w', false));
            $errorOutput->setFormatter($this->output->getFormatter());
            $errorOutput->setVerbosity($this->output->getVerbosity());
            $errorOutput->setDecorated($this->output->isDecorated());

            $reflectedOutput = new \ReflectionObject($this->output);
            $strErrProperty = $reflectedOutput->getProperty('stderr');
            $strErrProperty->setAccessible(true);
            $strErrProperty->setValue($this->output, $errorOutput);

            $reflectedParent = $reflectedOutput->getParentClass();
            $streamProperty = $reflectedParent->getProperty('stream');
            $streamProperty->setAccessible(true);
            $streamProperty->setValue($this->output, fopen('php://memory', 'w', false));
        }
    }

    /**
     * @return resource
     */
    private static function createStream(array $inputs)
    {
        $stream = fopen('php://memory', 'r+', false);

        foreach ($inputs as $input) {
            fwrite($stream, $input . \PHP_EOL);
        }

        rewind($stream);

        return $stream;
    }
}
