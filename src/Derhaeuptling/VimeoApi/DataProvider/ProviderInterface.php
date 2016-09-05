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

interface ProviderInterface
{
    /**
     * Get the video data
     *
     * @param int $videoId
     *
     * @return array|null
     */
    public function getVideo($videoId);

    /**
     * Get the video image path
     *
     * @param int $videoId
     * @param int $index
     *
     * @return string|null
     */
    public function getVideoImage($videoId, $index);

    /**
     * Get the video images
     *
     * @param int $videoId
     *
     * @return array|null
     */
    public function getVideoImages($videoId);

    /**
     * Get the album
     *
     * @param int $albumId
     *
     * @return array
     */
    public function getAlbum($albumId);

    /**
     * Get the album data by video
     *
     * @param int $videoId
     *
     * @return array|null
     */
    public function getAlbumByVideo($videoId);

    /**
     * Get the album videos
     *
     * @param int    $albumId
     * @param string $sorting
     * @param string $direction
     *
     * @return array
     */
    public function getAlbumVideos($albumId, $sorting = null, $direction = null);

    /**
     * Extract the video ID from video data
     *
     * @param array $data
     *
     * @return int
     */
    public function extractVideoId(array $data);

    /**
     * Extract the album ID from album data
     *
     * @param array $data
     *
     * @return int
     */
    public function extractAlbumId(array $data);
}