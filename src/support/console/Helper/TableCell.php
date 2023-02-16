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

use support\console\Exception\InvalidArgumentException;

/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class TableCell
{
    private $value;
    private $options = [
        'rowspan' => 1,
        'colspan' => 1,
        'style' => null,
    ];

    public function __construct(string $value = '', array $options = [])
    {
        $this->value = $value;

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new InvalidArgumentException(sprintf('The TableCell does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        if (isset($options['style']) && !$options['style'] instanceof TableCellStyle) {
            throw new InvalidArgumentException('The style option must be an instance of "TableCellStyle".');
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Returns the cell value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Gets number of colspan.
     *
     * @return int
     */
    public function getColspan()
    {
        return (int) $this->options['colspan'];
    }

    /**
     * Gets number of rowspan.
     *
     * @return int
     */
    public function getRowspan()
    {
        return (int) $this->options['rowspan'];
    }

    public function getStyle(): ?TableCellStyle
    {
        return $this->options['style'];
    }
}
