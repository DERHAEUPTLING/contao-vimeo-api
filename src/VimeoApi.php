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

use Contao\Config;
use Contao\System;
use Vimeo\Vimeo;

class VimeoApi
{
    /**
     * Cache
     * @var VideoCache
     */
    protected $cache;

    /**
     * Api constructor.
     *
     * @param VideoCache $cache
     */
    public function __construct(VideoCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get the Vimeo client
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $accessToken
     *
     * @return Vimeo
     */
    public function getClient($clientId = null, $clientSecret = null, $accessToken = null)
    {
        $clientId     = $clientId ?: Config::get('vimeo_clientId');
        $clientSecret = $clientSecret ?: Config::get('vimeo_clientSecret');
        $accessToken  = $accessToken ?: Config::get('vimeo_accessToken');

        $client = new VimeoClient($clientId, $clientSecret, $accessToken);
        $client->setCache($this->cache);

        return $client;
    }

    /**
     * Get the video
     *
     * @param Vimeo  $client
     * @param string $videoId
     * @param bool   $albumData
     *
     * @return VimeoVideo
     */
    public function getVideo(Vimeo $client, $videoId, $albumData = true)
    {
        $cacheKey = 'video_' . $videoId;

        if ($this->cache->hasData($cacheKey)) {
            $videoData = $this->cache->getData($cacheKey);
        } else {
            try {
                $data = $client->request('/videos/'.$videoId);
            } catch (\Exception $e) {
                System::log(sprintf('Unable to fetch Vimeo video ID %s with error "%s"', $videoId, $e->getMessage()), __METHOD__, TL_ERROR);

                return null;
            }

            if ($data['status'] !== 200) {
                System::log(sprintf('Unable to fetch Vimeo video ID %s with error "%s" (status code: %s)', $videoId, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                return null;
            }

            $videoData = $data['body'];

            // Cache the video data
            $this->cache->setData($cacheKey, $videoData);
        }

        $video = new VimeoVideo($videoId, $videoData, $this->cache);

        // Include the album data
        if ($albumData === true) {
            $video->setAlbum($this->getAlbumByVideo($client, $videoId));
        }

        return $video;
    }

    /**
     * Get the video image
     *
     * @param Vimeo  $client
     * @param string $videoId
     * @param int    $index
     *
     * @return array|null
     *
     * @throws \InvalidArgumentException
     */
    public function getVideoImage(Vimeo $client, $videoId, $index)
    {
        if ($index < 1) {
            throw new \InvalidArgumentException('The image index cannot be smaller than 1');
        }

        $cacheKey = 'video_' . $videoId;

        // Check if the Video exists if the first image was requested
        if ($index === 1 && $this->cache->hasData($cacheKey)) {
            $videoData = $this->cache->getData($cacheKey);

            if (isset($videoData['pictures']['sizes'])) {
                return $videoData['pictures']['sizes'];
            }
        }

        $cacheKey = 'video_images_'.$videoId;

        if ($this->cache->hasData($cacheKey)) {
            $imageData = $this->cache->getData($cacheKey);
        } else {
            try {
                $data = $client->request('/videos/'.$videoId.'/pictures');
            } catch (\Exception $e) {
                System::log(sprintf('Unable to fetch image for Vimeo video ID %s with error "%s"', $videoId, $e->getMessage()), __METHOD__, TL_ERROR);

                return null;
            }

            if ($data['status'] !== 200) {
                System::log(sprintf('Unable to fetch image for Vimeo video ID %s with error "%s" (status code: %s)', $videoId, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                return null;
            }

            $imageData = $data['body'];

            // Cache the data
            $this->cache->setData($cacheKey, $imageData);
        }

        $index = $index - 1;

        return isset($imageData['data'][$index]['sizes']) ? $imageData['data'][$index]['sizes'] : $imageData['data'][0]['sizes'];
    }

    /**
     * Get the parent album of the video
     *
     * @param Vimeo  $client
     * @param string $videoId
     *
     * @return array
     */
    protected function getAlbumByVideo(Vimeo $client, $videoId)
    {
        $videoId  = (int)$videoId;
        $cacheKey = 'album_by_video_' . $videoId;

        if ($this->cache->hasData($cacheKey)) {
            $albumData = $this->cache->getData($cacheKey);
        } else {
            $endpoint = '/me/albums';

            do {
                try {
                    $data = $client->request($endpoint);
                } catch (\Exception $e) {
                    System::log(sprintf('Unable to fetch Vimeo albums from "%s" with error "%s"', $endpoint, $e->getMessage()), __METHOD__, TL_ERROR);

                    return [];
                }

                if ($data['status'] !== 200) {
                    System::log(sprintf('Unable to fetch Vimeo albums from "%s" with error "%s" (status code: %s)', $endpoint, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                    return [];
                }

                $albumData = [];

                // Find the video in the album
                foreach ($data['body']['data'] as $album) {
                    $albumId = (int)array_pop(trimsplit('/', $album['uri']));

                    foreach ($this->getAlbumVideosData($client, $albumId) as $video) {
                        $currentVideoId = (int)array_pop(trimsplit('/', $video['uri']));

                        // Video has been found in the album, break all loops
                        if ($videoId === $currentVideoId) {
                            $albumData = $album;
                            break 3;
                        }
                    }
                }

                $endpoint = $data['body']['paging']['next'];
            } while ($endpoint);

            // Unable to find the album data
            if (!$albumData) {
                return [];
            }

            // Cache the album data
            $this->cache->setData($cacheKey, $albumData);
        }

        return $albumData;
    }

    /**
     * Get the album
     *
     * @param Vimeo  $client
     * @param string $albumId
     *
     * @return array
     */
    public function getAlbum(Vimeo $client, $albumId)
    {
        $cacheKey = 'album_' . $albumId;

        if ($this->cache->hasData($cacheKey)) {
            $albumData = $this->cache->getData($cacheKey);
        } else {
            try {
                $data = $client->request('/albums/'.$albumId);
            } catch (\Exception $e) {
                System::log(sprintf('Unable to fetch Vimeo album ID %s with error "%s"', $albumId, $e->getMessage()), __METHOD__, TL_ERROR);

                return [];
            }

            if ($data['status'] !== 200) {
                System::log(sprintf('Unable to fetch Vimeo album ID %s with error "%s" (status code: %s)', $albumId, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                return [];
            }

            $albumData = $data['body'];

            // Cache the album data
            $this->cache->setData($cacheKey, $albumData);
        }

        return (array) $albumData;
    }

    /**
     * Get the album videos
     *
     * @param Vimeo  $client
     * @param string $albumId
     * @param bool   $albumData
     * @param string $sorting
     * @param string $direction
     *
     * @return array
     */
    public function getAlbumVideos(Vimeo $client, $albumId, $albumData = true, $sorting = null, $direction = null)
    {
        $albumVideosData = $this->getAlbumVideosData($client, $albumId, $sorting, $direction);

        if (count($albumVideosData) === 0) {
            return [];
        }

        $album = [];

        // Get the album data
        if ($albumData === true) {
            $album = $this->getAlbum($client, $albumId);
        }

        $videos = [];

        // Generate videos
        foreach ($albumVideosData['data'] as $videoData) {
            $videoId = (int)str_replace('/videos/', '', $videoData['uri']);
            $video = new VimeoVideo($videoId, $videoData, $this->cache);

            // Include the album data
            if ($albumData === true) {
                $video->setAlbum($album);
            }

            $videos[] = $video;
        }

        return $videos;
    }

    /**
     * Get the album videos data
     *
     * @param Vimeo  $client
     * @param string $albumId
     * @param string $sorting
     * @param string $direction
     *
     * @return array
     */
    protected function getAlbumVideosData(Vimeo $client, $albumId, $sorting = null, $direction = null)
    {
        $cacheKey = 'album_videos_'.$albumId.($sorting ? ('_'.$sorting) : '').($direction ? ('_'.$direction) : '');

        if ($this->cache->hasData($cacheKey)) {
            $albumVideosData = $this->cache->getData($cacheKey);
        } else {
            $albumVideosData = [];
            $endpoint = '/albums/' . $albumId . '/videos';
            
            // Apply the sorting
            if ($sorting) {
                $endpoint .= '?sort='.$sorting.($direction ? ('&direction='.$direction) : '');
            }

            do {
                try {
                    $data = $client->request($endpoint);
                } catch (\Exception $e) {
                    System::log(sprintf('Unable to fetch Vimeo album ID %s with error "%s"', $albumId, $e->getMessage()), __METHOD__, TL_ERROR);

                    return [];
                }

                if ($data['status'] !== 200) {
                    System::log(sprintf('Unable to fetch Vimeo album ID %s with error "%s" (status code: %s)', $albumId, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                    return [];
                }

                // Initialize the data array
                if (count($albumVideosData) === 0) {
                    $albumVideosData = $data['body'];
                } else {
                    // Add videos to the array
                    foreach ($data['body']['data'] as $videoData) {
                        $albumVideosData['data'][] = $videoData;
                    }
                }

                $endpoint = $data['body']['paging']['next'];
            } while ($endpoint);

            // Cache the album videos data
            $this->cache->setData($cacheKey, $albumVideosData);
        }

        return $albumVideosData;
    }
}