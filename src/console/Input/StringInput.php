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

namespace Triangle\Engine\Console\Input;

use Triangle\Engine\Console\Exception\InvalidArgumentException;

/**
 * StringInput represents an input provided as a string.
 *
 * Usage:
 *
 *     $input = new StringInput('foo --bar="foobar"');
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class StringInput extends ArgvInput
{
    public const REGEX_STRING = '([^\s]+?)(?:\s|(?<!\\\\)"|(?<!\\\\)\'|$)';
    public const REGEX_UNQUOTED_STRING = '([^\s\\\\]+?)';
    public const REGEX_QUOTED_STRING = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';

    /**
     * @param string $input A string representing the parameters from the CLI
     */
    public function __construct(string $input)
    {
        parent::__construct([]);

        $this->setTokens($this->tokenize($input));
    }

    /**
     * Tokenizes a string.
     *
     * @throws InvalidArgumentException When unable to parse input (should never happen)
     */
    private function tokenize(string $input): array
    {
        $tokens = [];
        $length = \strlen($input);
        $cursor = 0;
        $token = null;
        while ($cursor < $length) {
            if ('\\' === $input[$cursor]) {
                $token .= $input[++$cursor] ?? '';
                ++$cursor;
                continue;
            }

            if (preg_match('/\s+/A', $input, $match, 0, $cursor)) {
                if (null !== $token) {
                    $tokens[] = $token;
                    $token = null;
                }
            } elseif (preg_match('/([^="\'\s]+?)(=?)(' . self::REGEX_QUOTED_STRING . '+)/A', $input, $match, 0, $cursor)) {
                $token .= $match[1] . $match[2] . stripcslashes(str_replace(['"\'', '\'"', '\'\'', '""'], '', substr($match[3], 1, -1)));
            } elseif (preg_match('/' . self::REGEX_QUOTED_STRING . '/A', $input, $match, 0, $cursor)) {
                $token .= stripcslashes(substr($match[0], 1, -1));
            } elseif (preg_match('/' . self::REGEX_UNQUOTED_STRING . '/A', $input, $match, 0, $cursor)) {
                $token .= $match[1];
            } else {
                // should never happen
                throw new InvalidArgumentException(sprintf('Unable to parse input near "... %s ...".', substr($input, $cursor, 10)));
            }

            $cursor += \strlen($match[0]);
        }

        if (null !== $token) {
            $tokens[] = $token;
        }

        return $tokens;
    }
}
