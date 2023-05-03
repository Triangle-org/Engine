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

namespace Triangle\Engine\Console\Helper;

use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Triangle\Engine\Console\Output\OutputInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class Dumper
{
    private $output;
    private $dumper;
    private $cloner;
    private $handler;

    public function __construct(OutputInterface $output, CliDumper $dumper = null, ClonerInterface $cloner = null)
    {
        $this->output = $output;
        $this->dumper = $dumper;
        $this->cloner = $cloner;

        if (class_exists(CliDumper::class)) {
            $this->handler = function ($var): string {
                $dumper = $this->dumper ?? $this->dumper = new CliDumper(null, null, CliDumper::DUMP_LIGHT_ARRAY | CliDumper::DUMP_COMMA_SEPARATOR);
                $dumper->setColors($this->output->isDecorated());

                return rtrim($dumper->dump(($this->cloner ?? $this->cloner = new VarCloner())->cloneVar($var)->withRefHandles(false), true));
            };
        } else {
            $this->handler = function ($var): string {
                switch (true) {
                    case null === $var:
                        return 'null';
                    case true === $var:
                        return 'true';
                    case false === $var:
                        return 'false';
                    case \is_string($var):
                        return '"' . $var . '"';
                    default:
                        return rtrim(print_r($var, true));
                }
            };
        }
    }

    public function __invoke($var): string
    {
        return ($this->handler)($var);
    }
}
