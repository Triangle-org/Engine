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

namespace support;

/**
 * Session storage manager
 */
class Storage
{
    /**
     * Namespace
     *
     * @var string
     */
    protected string $storeNamespace = 'STORAGE';

    /**
     * Key prefix
     *
     * @var string
     */
    protected string $keyPrefix = '';


    /**
     * @throws \Exception
     */
    public function get($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (session()->has($this->storeNamespace) && isset(session()->get($this->storeNamespace)[$key])) {
            $value = session()->get($this->storeNamespace)[$key];

            if (is_array($value) && array_key_exists('lateObject', $value)) {
                $value = unserialize($value['lateObject']);
            }

            return $value;
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    public function set($key, $value): void
    {
        $key = $this->keyPrefix . strtolower($key);

        if (is_object($value)) {
            // We encapsulate as our classes may be defined after session is initialized.
            $value = ['lateObject' => serialize($value)];
        }

        $s = session()->get($this->storeNamespace);
        $s[$key] = $value;
        session()->put([$this->storeNamespace => $s]);
        session()->save();
    }

    /**
     * @throws \Exception
     */
    public function clear(): void
    {
        session()->delete($this->storeNamespace);
        session()->save();
    }

    /**
     * @throws \Exception
     */
    public function delete($key): void
    {
        $key = $this->keyPrefix . strtolower($key);

        if (session()->has($this->storeNamespace) && isset(session()->get($this->storeNamespace)[$key])) {
            $tmp = session()->get($this->storeNamespace);

            unset($tmp[$key]);

            session()->put([$this->storeNamespace => $tmp]);
            session()->save();
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMatch($key): void
    {
        $key = $this->keyPrefix . strtolower($key);

        if (session()->has($this->storeNamespace) && count(session()->get($this->storeNamespace))) {
            $tmp = session()->get($this->storeNamespace);

            foreach ($tmp as $k => $v) {
                if (strstr($k, $key)) {
                    unset($tmp[$k]);
                }
            }

            session()->put([$this->storeNamespace => $tmp]);
            session()->save();
        }
    }
}
