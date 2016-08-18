<?php

/**
 * vimeo_api extension for Contao Open Source CMS
 *
 * Copyright (C) 2016 derhaeuptling
 *
 * @author  derhaeuptling <https://derhaeuptling.com>
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace Derhaeuptling\VimeoApi\Maintenance;

use Derhaeuptling\VimeoApi\VideoCache;

class ClearCache extends VideoCache
{
    /**
     * Return true if there is data
     *
     * @param string   $key
     * @param callable $callback
     *
     * @return bool
     */
    public function hasData($key, callable $callback = null)
    {
        if ($callback !== null && parent::hasData($key)) {
            return $callback(parent::getData($key));
        }

        return false;
    }

    /**
     * Return true if there is image
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasImage($key)
    {
        return false;
    }
}