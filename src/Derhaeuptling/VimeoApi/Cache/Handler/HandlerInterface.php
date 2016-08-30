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

namespace Derhaeuptling\VimeoApi\Cache\Handler;

use Contao\ContentModel;
use Derhaeuptling\VimeoApi\DataProvider\ProviderInterface;

interface HandlerInterface
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
     * @param ProviderInterface $dataProvider
     * @param ContentModel      $contentElement
     *
     * @return bool
     */
    public function rebuild(ProviderInterface $dataProvider, ContentModel $contentElement);
}