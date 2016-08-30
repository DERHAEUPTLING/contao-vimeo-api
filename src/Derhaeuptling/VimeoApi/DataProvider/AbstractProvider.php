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

use Derhaeuptling\VimeoApi\Cache\Cache;
use Vimeo\Vimeo;

abstract class AbstractProvider
{
    /**
     * Per page items in the call
     * @var int
     */
    protected $perPage = 50;

    /**
     * Album fields to fetch
     * @var array
     */
    protected $albumFields = [
        'created_time',
        'description',
        'duration',
        'link',
        'modified_time',
        'name',
        'uri',
    ];

    /**
     * Video fields to fetch
     * @var array
     */
    protected $videoFields = [
        'created_time',
        'description',
        'duration',
        'height',
        'language',
        'link',
        'modified_time',
        'name',
        'pictures',
        'release_time',
        'uri',
        'width',
    ];

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Vimeo
     */
    protected $client;

    /**
     * AbstractProvider constructor.
     *
     * @param Cache $cache
     * @param Vimeo $client
     */
    public function __construct(Cache $cache, Vimeo $client)
    {
        $this->cache  = $cache;
        $this->client = $client;

        set_time_limit(0);
    }

    /**
     * Extract the video ID from video data
     *
     * @param array $data
     *
     * @return int
     */
    public function extractVideoId(array $data)
    {
        return (int)array_pop(trimsplit('/', $data['uri']));
    }

    /**
     * Extract the album ID from album data
     *
     * @param array $data
     *
     * @return int
     */
    public function extractAlbumId(array $data)
    {
        return (int)array_pop(trimsplit('/', $data['uri']));
    }

    /**
     * Fetch the video data
     *
     * @param int $videoId
     *
     * @return array|null
     */
    protected function fetchVideo($videoId)
    {
        $data = $this->client->request('/videos/'.$videoId, ['fields' => implode(',', $this->videoFields)]);

        if ($data['status'] !== 200) {
            return null;
        }

        return $data['body'];
    }

    /**
     * Fetch the album
     *
     * @param int $albumId
     *
     * @return array|null
     */
    protected function fetchAlbum($albumId)
    {
        $data = $this->client->request('/albums/'.$albumId, ['fields' => implode(',', $this->albumFields)]);

        if ($data['status'] !== 200) {
            return null;
        }

        return $data['body'];
    }

    /**
     * Fetch the album videos
     *
     * @param int    $albumId
     * @param string $sorting
     * @param string $direction
     *
     * @return array|null
     */
    protected function fetchAlbumVideos($albumId, $sorting = null, $direction = null)
    {
        $endpoint = '/albums/'.$albumId.'/videos';
        $params   = [
            'per_page' => $this->perPage,
            'fields'   => implode(',', $this->videoFields),
        ];

        // Apply the sorting
        if ($sorting) {
            $params['sort']      = $sorting;
            $params['direction'] = $direction;
        }

        $albumVideosData = [];

        do {
            $data = $this->client->request($endpoint, $params);

            // Reset the params as they will be appended to the next endpoint automatically by Vimeo
            $params = [];

            if ($data['status'] !== 200) {
                return null;
            }

            $albumVideosData = array_merge($albumVideosData, $data['body']['data']);
            $endpoint        = $data['body']['paging']['next'];
        } while ($endpoint);

        return $albumVideosData;
    }

    /**
     * Fetch the albums
     *
     * @return array|null
     */
    protected function fetchAlbums()
    {
        $albumsData = [];
        $endpoint   = '/me/albums';
        $params     = [
            'per_page' => $this->perPage,
            'fields'   => implode(',', $this->albumFields),
        ];

        do {
            $data = $this->client->request($endpoint, $params);

            // Reset the params as they will be appended to the next endpoint automatically by Vimeo
            $params = [];

            if ($data['status'] !== 200) {
                return null;
            }

            $albumsData = array_merge($albumsData, $data['body']['data']);
            $endpoint   = $data['body']['paging']['next'];
        } while ($endpoint);

        return $albumsData;
    }

    /**
     * Fetch the videos
     *
     * @return array|null
     */
    protected function fetchVideos()
    {
        $videosData = [];
        $endpoint   = '/me/videos';
        $params     = [
            'per_page' => $this->perPage,
            'fields'   => implode(',', $this->videoFields),
        ];

        do {
            $data = $this->client->request($endpoint, $params);

            // Reset the params as they will be appended to the next endpoint automatically by Vimeo
            $params = [];

            if ($data['status'] !== 200) {
                return null;
            }

            $videosData = array_merge($videosData, $data['body']['data']);
            $endpoint   = $data['body']['paging']['next'];
        } while ($endpoint);

        return $videosData;
    }

    /**
     * Fetch the video images
     *
     * @param int $videoId
     *
     * @return array|null
     */
    protected function fetchVideoImages($videoId)
    {
        $data = $this->client->request('/videos/'.$videoId.'/pictures');

        if ($data['status'] !== 200) {
            return null;
        }

        return $data['body'];
    }
}