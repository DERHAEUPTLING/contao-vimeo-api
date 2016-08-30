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

use Contao\Request;
use Contao\System;

class StandardProvider extends AbstractProvider implements ProviderInterface
{
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

        if ($this->cache->hasData($cacheKey)) {
            $videoData = $this->cache->getData($cacheKey);
        } else {
            if (($videoData = $this->fetchVideo($videoId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $videoData);
        }

        return $videoData;
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

        if ($this->cache->hasData($cacheKey)) {
            $albumData = $this->cache->getData($cacheKey);
        } else {
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
        }

        return $albumData;
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

        if ($this->cache->hasData($cacheKey)) {
            $albumData = $this->cache->getData($cacheKey);
        } else {
            if (($albumData = $this->fetchAlbum($albumId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $albumData);
        }

        return $albumData;
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

        if ($this->cache->hasData($cacheKey)) {
            $albumVideosData = $this->cache->getData($cacheKey);
        } else {
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

            // Cache the album videos data
            $this->cache->setData($cacheKey, $albumVideosData);
        }

        return $albumVideosData;
    }

    /**
     * Get the albums
     *
     * @return array|null
     */
    protected function getAlbums()
    {
        $cacheKey = 'albums';

        if ($this->cache->hasData($cacheKey)) {
            $albumsData = $this->cache->getData($cacheKey);
        } else {
            if (($albumsData = $this->fetchAlbums()) === null) {
                return null;
            }

            // Cache the album data by the way
            foreach ($albumsData as $albumData) {
                $this->cache->setData('album_'.$this->extractAlbumId($albumData), $albumData);
            }

            // Cache the albums data
            $this->cache->setData($cacheKey, $albumsData);
        }

        return $albumsData;
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

        if (!$this->cache->hasImage($cacheKey)) {
            if (($image = $this->fetchVideoImage($videoId, $index)) === null) {
                return null;
            }

            $this->cache->setImage($cacheKey, $image);
        }

        return $this->cache->getImage($cacheKey);
    }

    /**
     * Fetch the video image
     *
     * @param int $videoId
     * @param int $index
     *
     * @return null|string
     *
     * @throws \InvalidArgumentException
     */
    protected function fetchVideoImage($videoId, $index)
    {
        if ($index < 1) {
            throw new \InvalidArgumentException('The image index cannot be smaller than 1');
        }

        $videoId = (int)$videoId;

        // Return if the video could not be found
        if (($videoData = $this->getVideo($videoId)) === null) {
            return null;
        }

        $index = (int)$index - 1;

        // If the first image was requested then it should already be there
        if ($index === 0 && isset($videoData['pictures']['sizes'])) {
            $sizes = $videoData['pictures']['sizes'];
        } else {
            // Return if there are no video images
            if (($images = $this->getVideoImages($videoId)) === null) {
                return null;
            }

            $sizes = isset($images['data'][$index]['sizes']) ? $images['data'][$index]['sizes'] : $images['data'][0]['sizes'];
        }

        $picture = array_pop($sizes);
        $request = new Request();
        $request->send($picture['link']);

        if ($request->hasError()) {
            System::log(
                sprintf('Unable to download Vimeo video image "%s"', $picture['link']),
                __METHOD__,
                TL_ERROR
            );

            return null;
        }

        return $request->response;
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

        if ($this->cache->hasData($cacheKey)) {
            $imageData = $this->cache->getData($cacheKey);
        } else {
            if (($imageData = $this->fetchVideoImages($videoId)) === null) {
                return null;
            }

            $this->cache->setData($cacheKey, $imageData);
        }

        return $imageData['data'];
    }
}