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

use Contao\ContentModel;
use Derhaeuptling\VimeoApi\VimeoApi;

interface CacheRebuildInterface
{
    /**
     * Return true if the element is eligible for rebuild
     *
     * @param array $data
     *
     * @return bool
     */
    public function isEligible(array $data);

    /**
     * Rebuild the cache and return true on success, false otherwise
     *
     * @param VimeoApi     $api
     * @param ContentModel $contentElement
     *
     * @return bool
     */
    public function rebuild(VimeoApi $api, ContentModel $contentElement);
}