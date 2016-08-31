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

namespace Derhaeuptling\VimeoApi;

use Derhaeuptling\VimeoApi\DataProvider\ProviderInterface;

class Factory
{
    /**
     * @var ProviderInterface
     */
    protected $dataProvider;

    /**
     * ElementFactory constructor.
     *
     * @param ProviderInterface $dataProvider
     */
    public function __construct(ProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Create the video element
     *
     * @param int  $videoId
     * @param bool $includeAlbum
     *
     * @return Video|null
     */
    public function createVideo($videoId, $includeAlbum = true)
    {
        if (($videoData = $this->dataProvider->getVideo($videoId)) === null) {
            return null;
        }

        $video = new Video($videoId, $videoData);

        if ($includeAlbum) {
            if (($albumData = $this->dataProvider->getAlbumByVideo($videoId)) === null) {
                return null;
            }

            if (($album = $this->createAlbum($this->dataProvider->extractAlbumId($albumData))) === null) {
                return null;
            }

            $video->setAlbum($album);
        }

        return $video;
    }

    /**
     * Create the album
     *
     * @param int    $albumId
     * @param bool   $includeVideos
     * @param string $sorting
     * @param string $direction
     *
     * @return Album|null
     */
    public function createAlbum($albumId, $includeVideos = false, $sorting = null, $direction = null)
    {
        if (($data = $this->dataProvider->getAlbum($albumId)) === null) {
            return null;
        }

        $videos = [];

        if ($includeVideos) {
            if (($videosData = $this->dataProvider->getAlbumVideos($albumId, $sorting, $direction)) === null) {
                return null;
            }

            foreach ($videosData as $videoData) {
                if (($video = $this->createVideo($this->dataProvider->extractVideoId($videoData), false)) !== null) {
                    $videos[] = $video;
                }
            }
        }

        return new Album($albumId, $data, $videos);
    }
}