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
     *
     * @return VimeoVideo
     */
    public function getVideo(Vimeo $client, $videoId)
    {
        $cacheKey = 'video_' . $videoId;

        if ($this->cache->hasData($cacheKey) === true) {
            $videoData = $this->cache->getData($cacheKey);
        } else {
            $data = $client->request('/videos/' . $videoId);

            if ($data['status'] !== 200) {
                System::log(sprintf('Unable to fetch Vimeo video ID %s with error "%s" (status code: %s)', $videoId, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                return null;
            }

            $videoData = $data['body'];

            // Cache the video data
            $this->cache->setData($cacheKey, $videoData);
        }

        return new VimeoVideo($videoId, $videoData, $this->cache);
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
     *
     * @return array
     */
    public function getAlbumVideos(Vimeo $client, $albumId)
    {
        $cacheKey = 'album_videos_' . $albumId;

        if ($this->cache->hasData($cacheKey) === true) {
            $albumVideosData = $this->cache->getData($cacheKey);
        } else {
            $data = $client->request('/albums/' . $albumId . '/videos');

            if ($data['status'] !== 200) {
                System::log(sprintf('Unable to fetch Vimeo album ID %s with error "%s" (status code: %s)', $albumId, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                return [];
            }

            $albumVideosData = $data['body'];

            // Cache the album videos data
            $this->cache->setData($cacheKey, $albumVideosData);
        }

        if ($albumVideosData === null) {
            return [];
        }

        $videos = [];

        // Generate videos
        foreach ($albumVideosData['data'] as $videoData) {
            $videoId  = (int)str_replace('/videos/', '', $videoData['uri']);
            $videos[] = new VimeoVideo($videoId, $videoData, $this->cache);
        }

        return $videos;
    }
}