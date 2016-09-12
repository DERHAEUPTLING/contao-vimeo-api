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

use Contao\Config;

class BatchRebuildProvider extends StandardProvider implements ProviderInterface
{
    /**
     * Mark the cache as obsolete on initialization
     */
    public function init()
    {
        $this->cache->markAllObsolete();
        $this->preFetchAlbums();
        $this->preFetchVideos();
    }

    /**
     * Pre-fetch the albums to save the number of API calls
     */
    protected function preFetchAlbums()
    {
        if (($albums = $this->getAlbums()) === null) {
            return;
        }

        $this->cache->setData('albums', $albums);

        foreach ($albums as $album) {
            $albumId  = $this->extractAlbumId($album);
            $cacheKey = 'album_'.$albumId;

            $this->cache->setData($cacheKey, $album);

            // Rebuild the album videos
            $this->getAlbumVideos($albumId);
        }
    }

    /**
     * Pre-fetch the videos to save the number of API calls
     */
    protected function preFetchVideos()
    {
        if (($videos = $this->fetchVideos()) === null) {
            return;
        }

        foreach ($videos as $video) {
            $videoId  = $this->extractVideoId($video);
            $cacheKey = 'video_'.$videoId;

            // Get the cached video before overriding its data
            if ($this->cache->hasData($cacheKey)) {
                $cachedVideo = $this->cache->getData($cacheKey);
            } else {
                $cachedVideo = null;
            }

            $this->cache->setData($cacheKey, $video);

            // Get the video images only if the video has been not cached yet or has been modified since last time
            if ($cachedVideo === null || $cachedVideo['modified_time'] !== $video['modified_time']) {
                $this->getVideoImage($videoId, Config::get('vimeo_imageIndex'));
            }
        }
    }

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

        // Check if the given video has been fetched
        if ($this->cache->isDataObsolete($cacheKey)) {
            if (($video = $this->fetchVideo($videoId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $video);
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
        $cacheKey = 'album_video_';

        if ($this->cache->isDataObsolete($cacheKey)) {
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
            $this->cache->setReference($cacheKey, 'album_'.$this->extractAlbumId($albumData));
        }

        return parent::getAlbumByVideo($videoId);
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

        if ($this->cache->isDataObsolete($cacheKey)) {
            if (($album = $this->fetchAlbum($albumId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $album);
        }

        return parent::getAlbum($albumId);
    }

    /**
     * Get the album videos
     *
     * @param int    $albumId
     * @param string $sorting
     * @param string $direction
     *
     * @return array|null
     */
    public function getAlbumVideos($albumId, $sorting = null, $direction = null)
    {
        $albumId  = (int)$albumId;
        $cacheKey = 'album_videos_'.$albumId.($sorting ? ('_'.$sorting) : '').($direction ? ('_'.$direction) : '');

        if ($this->cache->isDataObsolete($cacheKey)) {
            if (($albumVideosData = $this->fetchAlbumVideos($albumId, $sorting, $direction)) === null) {
                return null;
            }

            // Update the video data by the way
            foreach ($albumVideosData as $video) {
                $videoId = $this->extractVideoId($video);
                $this->cache->setData('video_'.$videoId, $video);

                // Also set album by video data
                if (($albumData = $this->getAlbum($albumId)) !== null) {
                    $this->cache->setReference('album_video_'.$videoId, 'album_'.$this->extractAlbumId($albumData));
                }
            }

            $this->cache->setData($cacheKey, $albumVideosData);
        }

        return parent::getAlbumVideos($albumId, $sorting, $direction);
    }

    /**
     * Get the albums
     *
     * @return array|null
     */
    protected function getAlbums()
    {
        $cacheKey = 'albums';

        if ($this->cache->isDataObsolete($cacheKey)) {
            if (($albums = $this->fetchAlbums()) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $albums);
        }

        return parent::getAlbums();
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
        $videoId = (int)$videoId;
        $index   = (int)$index;

        if (($image = $this->fetchVideoImage($videoId, $index)) === null) {
            return null;
        }

        $this->cache->setImage('video_'.$videoId.'_'.$index, $image);

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

        if ($this->cache->isDataObsolete($cacheKey)) {
            if (($images = $this->fetchVideoImages($videoId)) === null) {
                return null;
            }

            $this->cache->setData('video_images_'.$videoId, $images);
        }

        return parent::getVideoImages($videoId);
    }
}