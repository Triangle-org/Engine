<?php

namespace support\telegram\Objects\Passport;

use support\telegram\Objects\BaseObject;

/**
 * @link https://core.telegram.org/bots/api#passportscope
 *
 * @property PassportScopeElement[]  $data            List of requested elements, each type may be used only once in the entire array of PassportScopeElement objects
 * @property int                     $v               Scope version, must be 1
 */
class PassportScope extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
        ];
    }
}
