<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
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
