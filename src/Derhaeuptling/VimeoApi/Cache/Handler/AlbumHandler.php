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
use Derhaeuptling\VimeoApi\Video;

class AlbumHandler implements HandlerInterface
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
        return $data['vimeo_albumId'] ? true : false;
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
        $album   = $factory->createAlbum(
            $contentElement->vimeo_albumId,
            true,
            $contentElement->vimeo_sorting,
            $contentElement->vimeo_sortingDirection
        );

        if ($album === null) {
            return false;
        }

        /** @var Video $video */
        foreach ($album->getVideos() as $video) {
            if (Config::get('vimeo_allImages') && $dataProvider->getVideoImages($video->getId()) === null) {
                return false;
            }

            if ($dataProvider->getVideoImage($video->getId(), Config::get('vimeo_imageIndex')) === null) {
                return false;
            }
        }

        return true;
    }
}