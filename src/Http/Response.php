<?php

/**
 * @author    localzet<creator@localzet.ru>
 * @copyright localzet<creator@localzet.ru>
 * @link      https://www.localzet.ru/
 * @license   https://www.localzet.ru/license GNU GPLv3 License
 */

namespace localzet\FrameX\Http;

use localzet\FrameX\App;

/**
 * Class Response
 * @package localzet\FrameX\Http
 */
class Response extends \localzet\V3\Protocols\Http\Response
{
    /**
     * @param string $file
     * @return $this
     */
    public function file($file)
    {
        if ($this->notModifiedSince($file)) {
            return $this->withStatus(304);
        }
        return $this->withFile($file);
    }

    /**
     * @param string $file
     * @param string $download_name
     * @return $this
     */
    public function download($file, $download_name = '')
    {
        $this->withFile($file);
        if ($download_name) {
            $this->header('Content-Disposition', "attachment; filename=\"$download_name\"");
        }
        return $this;
    }

    /**
     * @param $file
     * @return bool
     */
    protected function notModifiedSince($file)
    {
        $if_modified_since = App::request()->header('if-modified-since');
        if ($if_modified_since === null || !($mtime = \filemtime($file))) {
            return false;
        }
        return $if_modified_since === \gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }
}
