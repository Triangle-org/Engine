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

namespace Triangle\Engine\Console\Formatter;

use Triangle\Engine\Console\Color;

/**
 * Formatter style class for defining styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutputFormatterStyle implements OutputFormatterStyleInterface
{
    private $color;
    private $foreground;
    private $background;
    private $options;
    private $href;
    private $handlesHrefGracefully;

    /**
     * Initializes output formatter style.
     *
     * @param string|null $foreground The style foreground color name
     * @param string|null $background The style background color name
     */
    public function __construct(string $foreground = null, string $background = null, array $options = [])
    {
        $this->color = new Color($this->foreground = $foreground ?: '', $this->background = $background ?: '', $this->options = $options);
    }

    /**
     * {@inheritdoc}
     */
    public function setForeground(string $color = null)
    {
        $this->color = new Color($this->foreground = $color ?: '', $this->background, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackground(string $color = null)
    {
        $this->color = new Color($this->foreground, $this->background = $color ?: '', $this->options);
    }

    public function setHref(string $url): void
    {
        $this->href = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption(string $option)
    {
        $this->options[] = $option;
        $this->color = new Color($this->foreground, $this->background, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetOption(string $option)
    {
        $pos = array_search($option, $this->options);
        if (false !== $pos) {
            unset($this->options[$pos]);
        }

        $this->color = new Color($this->foreground, $this->background, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->color = new Color($this->foreground, $this->background, $this->options = $options);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(string $text)
    {
        if (null === $this->handlesHrefGracefully) {
            $this->handlesHrefGracefully = 'JetBrains-JediTerm' !== getenv('TERMINAL_EMULATOR')
                && (!getenv('KONSOLE_VERSION') || (int)getenv('KONSOLE_VERSION') > 201100);
        }

        if (null !== $this->href && $this->handlesHrefGracefully) {
            $text = "\033]8;;$this->href\033\\$text\033]8;;\033\\";
        }

        return $this->color->apply($text);
    }
}
