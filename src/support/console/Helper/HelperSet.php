<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\console\Helper;

use support\console\Command\Command;
use support\console\Exception\InvalidArgumentException;

/**
 * HelperSet represents a set of helpers to be used with a command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @implements \IteratorAggregate<string, Helper>
 */
class HelperSet implements \IteratorAggregate
{
    /** @var array<string, Helper> */
    private $helpers = [];
    private $command;

    /**
     * @param Helper[] $helpers An array of helper
     */
    public function __construct(array $helpers = [])
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, \is_int($alias) ? null : $alias);
        }
    }

    public function set(HelperInterface $helper, string $alias = null)
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }

        $helper->setHelperSet($this);
    }

    /**
     * Returns true if the helper if defined.
     *
     * @return bool
     */
    public function has(string $name)
    {
        return isset($this->helpers[$name]);
    }

    /**
     * Gets a helper value.
     *
     * @return HelperInterface
     *
     * @throws InvalidArgumentException if the helper is not defined
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The helper "%s" is not defined.', $name));
        }

        return $this->helpers[$name];
    }

    /**
     * @deprecated since Symfony 5.4
     */
    public function setCommand(Command $command = null)
    {
        trigger_deprecation('symfony/console', '5.4', 'Method "%s()" is deprecated.', __METHOD__);

        $this->command = $command;
    }

    /**
     * Gets the command associated with this helper set.
     *
     * @return Command
     *
     * @deprecated since Symfony 5.4
     */
    public function getCommand()
    {
        trigger_deprecation('symfony/console', '5.4', 'Method "%s()" is deprecated.', __METHOD__);

        return $this->command;
    }

    /**
     * @return \Traversable<string, Helper>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->helpers);
    }
}