<?php

namespace support\telegram\Objects;

/**
 * Class Voice.
 *
 * @link https://core.telegram.org/bots/api#voice
 *
 * @property string         $fileId         Identifier for this file, which can be used to download or reuse the file.
 * @property string         $fileUniqueId   Unique identifier for this file, which is supposed to be the same over time and for different bots. Can't be used to download or reuse the file.
 * @property int            $duration       Duration of the audio in seconds as defined by sender.
 * @property string|null    $mimeType       (Optional) MIME type of the file as defined by sender.
 * @property int|null       $fileSize       (Optional) File size in bytes. It can be bigger than 2^31 and some programming 
 *                                                      languages may have difficulty/silent defects in interpreting it. But it has at most 52 significant bits, 
 *                                                      so a signed 64-bit integer or double-precision float type are safe for storing this value.
 */
class Voice extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
