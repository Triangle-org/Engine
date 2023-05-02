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

namespace support\telegram\Answers;

use support\telegram\Traits\Telegram;

/**
 * Class AnswerBus.
 */
abstract class AnswerBus
{
    use Telegram;

    /**
     * Handle calls to missing methods.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $parameters);
        }

        throw new \BadMethodCallException("Method [$method] does not exist.");
    }

    /**
     * Use PHP Reflection and Laravel Container to instantiate the answer with type hinted dependencies.
     *
     * @param $answerClass
     *
     * @return object
     */
    protected function buildDependencyInjectedAnswer($answerClass)
    {
        // check if the command has a constructor
        if (!method_exists($answerClass, '__construct')) {
            return new $answerClass();
        }

        // get constructor params
        $constructorReflector = new \ReflectionMethod($answerClass, '__construct');
        $params = $constructorReflector->getParameters();

        // if no params are needed proceed with normal instantiation
        if (empty($params)) {
            return new $answerClass();
        }

        // otherwise fetch each dependency out of the container
        $container = $this->telegram->getContainer();
        $dependencies = [];
        foreach ($params as $param) {
            if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
                $dependencies[] = $container->make($param->getType()->getName());
            } else {
                $dependencies[] = $container->make($param->getClass()->name);
            }
        }

        // and instantiate the object with dependencies through ReflectionClass
        $classReflector = new \ReflectionClass($answerClass);

        return $classReflector->newInstanceArgs($dependencies);
    }
}
