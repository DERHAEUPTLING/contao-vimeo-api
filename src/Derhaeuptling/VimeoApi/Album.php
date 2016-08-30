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

class Album
{
    /**
     * Album ID
     * @var string
     */
    protected $id;

    /**
     * Data
     * @var array
     */
    protected $data = [];

    /**
     * Videos
     * @var array
     */
    protected $videos = [];

    /**
     * Album constructor.
     *
     * @param string $id
     * @param array  $data
     * @param array  $videos
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($id, array $data, array $videos)
    {
        $this->id     = $id;
        $this->data   = $data;
        $this->videos = $videos;

        /** @var Video $video */
        foreach ($videos as $video) {
            if (!($video instanceof Video)) {
                throw new \InvalidArgumentException('The provided videos must be an instance of Video class');
            }

            $video->setAlbum($this);
        }
    }

    /**
     * Get the data property
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * Get the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the video ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the videos
     *
     * @return array
     */
    public function getVideos()
    {
        return $this->videos;
    }
}