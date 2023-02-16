<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
 * 
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.localzet.com/license GNU GPLv3 License
 */

namespace support\telegram\Traits;

use support\telegram\Api;

/**
 * Class Telegram.
 */
trait Telegram
{
    /** @var Api Holds the Super Class Instance. */
    protected $telegram = null;

    /**
     * Returns Super Class Instance.
     *
     * @return Api
     */
    public function getTelegram(): Api
    {
        return $this->telegram;
    }

    /**
     * Set Telegram Api Instance.
     *
     * @param Api $telegram
     *
     * @return $this
     */
    public function setTelegram(Api $telegram)
    {
        $this->telegram = $telegram;

        return $this;
    }
}
