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

namespace Derhaeuptling\VimeoApi\DataProvider;

class SingleRebuildProvider extends StandardProvider implements ProviderInterface
{
    /**
     * Rebuilt data (so it does not get rebuild twice upon second method call)
     * @var array
     */
    protected $rebuilt = [];

    /**
     * Get the video data
     *
     * @param int $videoId
     *
     * @return array|null
     */
    public function getVideo($videoId)
    {
        $videoId  = (int)$videoId;
        $cacheKey = 'video_'.$videoId;

        if (!$this->isRebuilt($cacheKey)) {
            if (($video = $this->fetchVideo($videoId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $video);
            $this->setRebuilt($cacheKey);
        }

        return parent::getVideo($videoId);
    }

    /**
     * Get the album data by video
     *
     * @param int $videoId
     *
     * @return array|null
     */
    public function getAlbumByVideo($videoId)
    {
        $videoId  = (int)$videoId;
        $cacheKey = 'album_video_'.$videoId;

        if (!$this->isRebuilt($cacheKey)) {
            if (($albums = $this->getAlbums()) === null) {
                return null;
            }

            $albumData = [];

            // Find the video in the album
            foreach ($albums as $album) {
                $albumId = $this->extractAlbumId($album);

                foreach ($this->getAlbumVideos($albumId, null, null) as $video) {
                    // Video has been found in the album, break all loops
                    if ($videoId === $this->extractVideoId($video)) {
                        $albumData = $album;
                        break 2;
                    }
                }
            }

            // Set the reference to album data
            if (count($albumData) > 0) {
                $this->cache->setReference($cacheKey, 'album_'.$this->extractAlbumId($albumData));
            } else {
                // If the video has no album then just set an empty array
                $this->cache->setData($cacheKey, $albumData);
            }

            $this->setRebuilt($cacheKey);
        }

        return parent::getAlbumByVideo($videoId);
    }

    /**
     * Get the albums
     *
     * @return array|null
     */
    protected function getAlbums()
    {
        $cacheKey = 'albums';

        if (!$this->isRebuilt($cacheKey)) {
            if (($albums = $this->fetchAlbums()) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $albums);

            foreach ($albums as $album) {
                $albumId = $this->extractAlbumId($album);
                $key     = 'album_'.$albumId;

                $this->cache->setData($key, $album);
                $this->setRebuilt($key);

                // Rebuild the album videos
                $this->rebuildAlbumVideos($albumId);
            }

            $this->setRebuilt($cacheKey);
        }

        return parent::getAlbums();
    }

    /**
     * Get the album
     *
     * @param int $albumId
     *
     * @return array|null
     */
    public function getAlbum($albumId)
    {
        $albumId  = (int)$albumId;
        $cacheKey = 'album_'.$albumId;

        if (!$this->isRebuilt($cacheKey)) {
            if (($albumData = $this->fetchAlbum($albumId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $albumData);
            $this->setRebuilt($cacheKey);

            // Rebuild the album videos
            $this->rebuildAlbumVideos($albumId);
        }

        return parent::getAlbum($albumId);
    }

    /**
     * Rebuild the album videos
     *
     * @param int    $albumId
     * @param string $sorting
     * @param string $direction
     *
     * @return array|null
     */
    protected function rebuildAlbumVideos($albumId, $sorting = null, $direction = null)
    {
        $albumId  = (int)$albumId;
        $cacheKey = 'album_videos_'.$albumId.($sorting ? ('_'.$sorting) : '').($direction ? ('_'.$direction) : '');

        if (!$this->isRebuilt($cacheKey)) {
            if (($albumVideosData = $this->fetchAlbumVideos($albumId, $sorting, $direction)) === null) {
                return null;
            }

            // Update the video data by the way
            foreach ($albumVideosData as $video) {
                $videoId = $this->extractVideoId($video);
                $key     = 'video_'.$videoId;

                $this->cache->setData($key, $video);
                $this->setRebuilt($key);

                // Also set album by video data
                if (($albumData = $this->getAlbum($albumId)) !== null) {
                    $this->cache->setReference('album_video_'.$videoId, 'album_'.$this->extractAlbumId($albumData));
                }
            }

            $this->cache->setData($cacheKey, $albumVideosData);
            $this->setRebuilt($cacheKey);
        }
    }

    /**
     * Get the video image path
     *
     * @param int $videoId
     * @param int $index
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    public function getVideoImage($videoId, $index)
    {
        $videoId  = (int)$videoId;
        $index    = (int)$index;
        $cacheKey = 'video_'.$videoId.'_'.$index;

        if (!$this->isRebuilt($cacheKey)) {
            if (($image = $this->fetchVideoImage($videoId, $index)) === null) {
                return null;
            }

            $this->cache->setImage($cacheKey, $image);
            $this->setRebuilt($cacheKey);
        }

        return parent::getVideoImage($videoId, $index);
    }

    /**
     * Get the video images
     *
     * @param int $videoId
     *
     * @return array|null
     */
    public function getVideoImages($videoId)
    {
        $videoId  = (int)$videoId;
        $cacheKey = 'video_images_'.$videoId;

        if (!$this->isRebuilt($cacheKey)) {
            if (($images = $this->fetchVideoImages($videoId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $images);
            $this->setRebuilt($cacheKey);
        }

        return parent::getVideoImages($videoId);
    }

    /**
     * Check if the cache key is rebuilt
     *
     * @param string $cacheKey
     *
     * @return bool
     */
    protected function isRebuilt($cacheKey)
    {
        return in_array($cacheKey, $this->rebuilt, true);
    }

    /**
     * Set the cache key as rebuilt
     *
     * @param string $cacheKey
     */
    protected function setRebuilt($cacheKey)
    {
        $this->rebuilt[] = $cacheKey;
    }
}