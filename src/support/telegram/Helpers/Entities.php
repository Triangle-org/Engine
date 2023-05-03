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

namespace support\telegram\Helpers;

/**
 * Class Entities.
 */
class Entities
{
    /** @var string Message or Caption */
    protected $text;
    /** @var array Entities from Telegram */
    protected $entities;
    /** @var int Formatting Mode: 0:Markdown | 1:HTML */
    protected $mode = 0;

    /**
     * Entities constructor.
     *
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @param string $text
     *
     * @return static
     */
    public static function format(string $text): self
    {
        return new static($text);
    }

    /**
     * @param array $entities
     *
     * @return $this
     */
    public function withEntities(array $entities): self
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * Format it to markdown style.
     *
     * @return string
     */
    public function toMarkdown(): string
    {
        $this->mode = 0;

        return $this->apply();
    }

    /**
     * Format it to HTML syntax.
     *
     * @return string
     */
    public function toHTML(): string
    {
        $this->mode = 1;

        return $this->apply();
    }

    /**
     * Apply format for given text and entities.
     *
     * @return mixed|string
     */
    protected function apply()
    {
        $syntax = $this->syntax();

        $this->entities = array_reverse($this->entities);
        foreach ($this->entities as $entity) {
            $value = mb_substr($this->text, $entity['offset'], $entity['length']);
            $type = $entity['type'];
            if (isset($syntax[$type])) {
                if ($type === 'text_link') {
                    $replacement = sprintf($syntax[$type][$this->mode], $value, $entity['url']);
                } else {
                    $replacement = sprintf(
                        $syntax[$type][$this->mode],
                        ($type === 'text_mention') ? $entity['user']['username'] : $value
                    );
                }
                $this->text = substr_replace($this->text, $replacement, $entity['offset'], $entity['length']);
            }
        }

        return $this->text;
    }

    /**
     * Formatting Syntax.
     *
     * @return array
     */
    protected function syntax(): array
    {
        // No need of any special formatting for these entity types.
        // 'url', 'bot_command', 'hashtag', 'cashtag', 'email', 'phone_number', 'mention'

        return [
            'bold' => ['*%s*', '<strong>%s</strong>'],
            'italic' => ['_%s_', '<i>%s</i>'],
            'code' => ['`%s`', '<code>%s</code>'],
            'pre' => ["```\n%s```", '<pre>%s</pre>'],
            'text_mention' => ['[%1$s](tg://user?id=%1$s)', '<a href="tg://user?id=%1$s">%1$s</a>'],
            'text_link' => ['[%s](%s)', '<a href="%2$s">%1$s</a>'],
        ];
    }
}
