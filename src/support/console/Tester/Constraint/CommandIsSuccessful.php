<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
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

namespace support\console\Tester\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use support\console\Command\Command;

final class CommandIsSuccessful extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'is successful';
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        return Command::SUCCESS === $other;
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        return 'the command '.$this->toString();
    }

    /**
     * {@inheritdoc}
     */
    protected function additionalFailureDescription($other): string
    {
        $mapping = [
            Command::FAILURE => 'Command failed.',
            Command::INVALID => 'Command was invalid.',
        ];

        return $mapping[$other] ?? sprintf('Command returned exit status %d.', $other);
    }
}
