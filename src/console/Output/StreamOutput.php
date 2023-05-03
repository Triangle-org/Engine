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

use Triangle\Engine\Console\Exception\InvalidArgumentException;
use Triangle\Engine\Console\Formatter\OutputFormatterInterface;

/**
 * StreamOutput writes the output to a given stream.
 *
 * Usage:
 *
 *     $output = new StreamOutput(fopen('php://stdout', 'w'));
 *
 * As `StreamOutput` can use any stream, you can also use a file:
 *
 *     $output = new StreamOutput(fopen('/path/to/output.log', 'a', false));
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class StreamOutput extends Output
{
    private $stream;

    /**
     * @param resource $stream A stream resource
     * @param int $verbosity The verbosity level (one of the VERBOSITY constants in OutputInterface)
     * @param bool|null $decorated Whether to decorate messages (null for auto-guessing)
     * @param OutputFormatterInterface|null $formatter Output formatter instance (null to use default OutputFormatter)
     *
     * @throws InvalidArgumentException When first argument is not a real stream
     */
    public function __construct($stream, int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = null, OutputFormatterInterface $formatter = null)
    {
        if (!\is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new InvalidArgumentException('The StreamOutput class needs a stream as its first argument.');
        }

        $this->stream = $stream;

        if (null === $decorated) {
            $decorated = $this->hasColorSupport();
        }

        parent::__construct($verbosity, $decorated, $formatter);
    }

    /**
     * Gets the stream attached to this StreamOutput instance.
     *
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite(string $message, bool $newline)
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }

        @fwrite($this->stream, $message);

        fflush($this->stream);
    }

    /**
     * Returns true if the stream supports colorization.
     *
     * Colorization is disabled if not supported by the stream:
     *
     * This is tricky on Windows, because Cygwin, Msys2 etc emulate pseudo
     * terminals via named pipes, so we can only check the environment.
     *
     * Reference: Composer\XdebugHandler\Process::supportsColor
     * https://github.com/composer/xdebug-handler
     *
     * @return bool true if the stream supports colorization, false otherwise
     */
    protected function hasColorSupport()
    {
        // Follow https://no-color.org/
        if (isset($_SERVER['NO_COLOR']) || false !== getenv('NO_COLOR')) {
            return false;
        }

        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }

        if (\DIRECTORY_SEPARATOR === '\\') {
            return (\function_exists('sapi_windows_vt100_support')
                    && @sapi_windows_vt100_support($this->stream))
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }

        return stream_isatty($this->stream);
    }
}
