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
        if ($this->cache->hasVideoData($videoId) === true) {
            $videoData = $this->cache->getVideoData($videoId);
        } else {
            $data = $client->request('/videos/' . $videoId);

            if ($data['status'] !== 200) {
                System::log(sprintf('Unable to fetch Vimeo video ID %s with error "%s" (status code: %s)', $videoId, $data['body']['error'], $data['status']), __METHOD__, TL_ERROR);

                return null;
            }

            $videoData = $data['body'];

            // Cache the video data
            $this->cache->setVideoData($videoId, $videoData);
        }

        return new VimeoVideo($videoId, $videoData, $this->cache);
    }
}