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

        return new Vimeo($clientId, $clientSecret, $accessToken);
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

        if ($this->cache->hasData($cacheKey) === true) {
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
     * Get the parent album of the video
     *
     * @param Vimeo  $client
     * @param string $videoId
     *
     * @return array
     */
    protected function getAlbumByVideo(Vimeo $client, $videoId)
    {
        $cacheKey = 'album_by_video_' . $videoId;

        if ($this->cache->hasData($cacheKey) === true) {
            $albumData = $this->cache->getData($cacheKey);
        } else {
            $endpoint = '/me/albums';

            do {
                try {
                    $data = $client->request($endpoint);
                } catch (\Exception $e) {
                    System::log(sprintf('Unable to fetch Vimeo albums from "/me/albums" with error "%s"', $e->getMessage()), __METHOD__, TL_ERROR);

                    return [];
                }

                if ($data['status'] !== 200) {
                    System::log(sprintf('Unable to fetch Vimeo albums from "/me/albums" with error "%s" (status code: %s)', $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                    return [];
                }

                $albumData = [];

                // Find the video in the album
                foreach ($data['body']['data'] as $subData) {
                    try {
                        $videoData = $client->request($subData['uri'].'/videos/'.$videoId);
                    } catch (\Exception $e) {
                        System::log(sprintf('Unable to fetch Vimeo video ID %s with error "%s"', $videoId, $e->getMessage()), __METHOD__, TL_ERROR);

                        continue;
                    }

                    if ($videoData['status'] === 200) {
                        $albumData = $subData;
                        break 2;
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

        if ($this->cache->hasData($cacheKey) === true) {
            $albumData = $this->cache->getData($cacheKey);
        } else {
            $data = $client->request('/albums/' . $albumId);

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
     *
     * @return array
     */
    public function getAlbumVideos(Vimeo $client, $albumId, $albumData = true)
    {
        $albumVideosData = $this->getAlbumVideosData($client, $albumId);

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
            $videoId  = (int)str_replace('/videos/', '', $videoData['uri']);
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
     *
     * @return array
     */
    protected function getAlbumVideosData(Vimeo $client, $albumId)
    {
        $cacheKey = 'album_videos_' . $albumId;

        if ($this->cache->hasData($cacheKey) === true) {
            $albumVideosData = $this->cache->getData($cacheKey);
        } else {
            $albumVideosData = [];
            $endpoint = '/albums/' . $albumId . '/videos';

            do {
                $data = $client->request($endpoint);

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