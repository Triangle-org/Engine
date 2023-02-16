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

namespace support;

/**
 * Parser
 *
 * Этот класс используется для разбора простого текста на объекты. 
 * Он используется адаптерами OAuth для преобразования ответов API провайдеров в более «управляемый» формат.
 * 
 */
final class Parser
{
    /**
     * Декодирует строку в объект.
     *
     * Этот метод сначала попытается проанализировать данные 
     * как строку JSON (поскольку большинство провайдеров используют этот формат), а затем XML и parse_str.
     *
     * @param string $raw
     *
     * @return mixed
     */
    public function parse($raw = null)
    {
        $data = $this->parseJson($raw);

        if (!$data) {
            $data = $this->parseXml($raw);

            if (!$data) {
                $data = $this->parseQueryString($raw);
            }
        }

        return $data;
    }

    /**
     * Декодирует строку JSON
     *
     * @param $result
     *
     * @return mixed
     */
    public function parseJson($result)
    {
        return json_decode($result);
    }

    /**
     * Декодирует строку XML
     *
     * @param $result
     *
     * @return mixed
     */
    public function parseXml($result)
    {
        libxml_use_internal_errors(true);

        $result = preg_replace('/([<\/])([a-z0-9-]+):/i', '$1', $result);
        $xml = simplexml_load_string($result);

        libxml_use_internal_errors(false);

        if (!$xml) {
            return [];
        }

        $arr = json_decode(json_encode((array)$xml), true);
        $arr = array($xml->getName() => $arr);

        return $arr;
    }

    /**
     * Разбирает строку на переменные
     *
     * @param $result
     *
     * @return \StdClass
     */
    public function parseQueryString($result)
    {
        parse_str($result, $output);

        if (!is_array($output)) {
            return $result;
        }

        $result = new \StdClass();

        foreach ($output as $k => $v) {
            $result->$k = $v;
        }

        return $result;
    }

    /**
     * Нужно улучшить
     *
     * @param $birthday
     * @param $seperator
     *
     * @return array
     */
    public function parseBirthday($birthday, $seperator)
    {
        $birthday = date_parse($birthday);

        return [$birthday['year'], $birthday['month'], $birthday['day']];
    }
}
