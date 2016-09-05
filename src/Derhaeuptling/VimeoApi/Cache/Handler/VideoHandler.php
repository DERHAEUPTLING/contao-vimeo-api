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

use Contao\Config;
use Contao\ContentModel;
use Derhaeuptling\VimeoApi\DataProvider\ProviderInterface;
use Derhaeuptling\VimeoApi\Factory;

class VideoHandler implements HandlerInterface
{
    /**
     * Return true if the element is eligible for rebuild
     *
     * @param array $data
     *
     * @return bool
     */
    public function isEligible(array $data)
    {
        return $data['vimeo_videoId'] ? true : false;
    }

    /**
     * Rebuild the cache and return true on success, false otherwise
     *
     * @param ProviderInterface $dataProvider
     * @param ContentModel      $contentElement
     *
     * @return bool
     */
    public function rebuild(ProviderInterface $dataProvider, ContentModel $contentElement)
    {
        $factory = new Factory($dataProvider);

        if (($video = $factory->createVideo($contentElement->vimeo_videoId)) === null) {
            return false;
        }

        if (Config::get('vimeo_allImages') && $dataProvider->getVideoImages($contentElement->vimeo_videoId) === null) {
            return false;
        }

        if ($dataProvider->getVideoImage($contentElement->vimeo_videoId, Config::get('vimeo_imageIndex')) === null) {
            return false;
        }

        return true;
    }
}