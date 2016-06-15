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

use Contao\Config;
use Contao\ContentModel;
use Derhaeuptling\VimeoApi\VimeoApi;
use Derhaeuptling\VimeoApi\VimeoVideo;

class AlbumCacheRebuilder implements CacheRebuildInterface
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
     * @param VimeoApi     $api
     * @param ContentModel $contentElement
     *
     * @return bool
     */
    public function rebuild(VimeoApi $api, ContentModel $contentElement)
    {
        $client = $api->getClient();

        if (($album = $api->getAlbum($client, $contentElement->vimeo_albumId)) === null) {
            return false;
        }

        /** @var VimeoVideo $video */
        foreach ($api->getAlbumVideos($client, $contentElement->vimeo_albumId) as $video) {
            if (($image = $api->getVideoImage($client, $video->getId(), Config::get('vimeo_imageIndex'))) === null) {
                return false;
            }

            $video->setPicturesData($image);
            $video->downloadPoster();
        }

        return true;
    }
}