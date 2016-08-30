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

        if (!in_array($cacheKey, $this->rebuilt, true)) {
            if (($video = $this->fetchVideo($videoId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $video);
            $this->rebuilt[] = $cacheKey;
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

        if (!in_array($cacheKey, $this->rebuilt, true)) {
            $albumData = [];

            // Find the video in the album
            foreach ($this->getAlbums() as $album) {
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
            $this->rebuilt[] = $cacheKey;
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

        if (!in_array($cacheKey, $this->rebuilt, true)) {
            if (($albums = $this->fetchAlbums()) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $albums);

            foreach ($albums as $album) {
                $albumId = $this->extractAlbumId($album);
                $key     = 'album_'.$albumId;

                // Get the cached album before overriding its data
                if ($this->cache->hasData($key)) {
                    $cachedAlbum = $this->cache->getData($key);
                } else {
                    $cachedAlbum = null;
                }

                $this->cache->setData($key, $album);
                $this->rebuilt[] = $key;

                // Get the album videos only if the album has been not cached yet or has been modified since last time
                if ($cachedAlbum === null || $cachedAlbum['modified_time'] !== $album['modified_time']) {
                    $this->rebuildAlbumVideos($albumId);
                }

                // Mark the videos as rebuilt
                foreach ($this->getAlbumVideos($albumId) as $video) {
                    $this->rebuilt[] = 'video_'.$this->extractVideoId($video);
                }
            }

            $this->rebuilt[] = $cacheKey;
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

        if (!in_array($cacheKey, $this->rebuilt, true)) {
            if (($albumData = $this->fetchAlbum($albumId)) === null) {
                return null;
            }

            // Get the cached album before overriding its data
            if ($this->cache->hasData($cacheKey)) {
                $cachedAlbum = $this->cache->getData($cacheKey);
            } else {
                $cachedAlbum = null;
            }

            $this->cache->setData($cacheKey, $albumData);

            // Get the album videos only if the album has been not cached yet or has been modified since last time
            if ($cachedAlbum === null || $cachedAlbum['modified_time'] !== $albumData['modified_time']) {
                $this->rebuildAlbumVideos($albumId);
            }

            // Mark the videos as rebuilt
            foreach ($this->getAlbumVideos($albumId) as $video) {
                $this->rebuilt[] = 'video_'.$this->extractVideoId($video);
            }

            $this->rebuilt[] = $cacheKey;
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

        if (!in_array($cacheKey, $this->rebuilt, true)) {
            if (($albumVideosData = $this->fetchAlbumVideos($albumId, $sorting, $direction)) === null) {
                return null;
            }

            // Update the video data by the way
            foreach ($albumVideosData as $video) {
                $videoId = $this->extractVideoId($video);
                $key     = 'video_'.$videoId;

                $this->cache->setData($key, $video);
                $this->rebuilt[] = $key;

                // Also set album by video data
                if (($albumData = $this->getAlbum($albumId)) !== null) {
                    $this->cache->setReference('album_video_'.$videoId, 'album_'.$this->extractAlbumId($albumData));
                }
            }

            $this->cache->setData($cacheKey, $albumVideosData);
            $this->rebuilt[] = $cacheKey;
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

        if (!in_array($cacheKey, $this->rebuilt, true)) {
            if (($image = $this->fetchVideoImage($videoId, $index)) === null) {
                return null;
            }

            $this->cache->setImage($cacheKey, $image);
            $this->rebuilt[] = $cacheKey;
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

        if (!in_array($cacheKey, $this->rebuilt, true)) {
            if (($images = $this->fetchVideoImages($videoId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $images);
            $this->rebuilt[] = $cacheKey;
        }

        return parent::getVideoImages($videoId);
    }
}