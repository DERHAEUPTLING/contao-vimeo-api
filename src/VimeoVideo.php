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

use Contao\Controller;
use Contao\FrontendTemplate;
use Contao\Request;
use Contao\System;

class VimeoVideo
{
    /**
     * Cache
     * @var VideoCache
     */
    protected $cache;

    /**
     * Video ID
     * @var string
     */
    protected $id;

    /**
     * Data
     * @var array
     */
    protected $data = [];

    /**
     * Albums data
     * @var array
     */
    protected $albums = [];

    /**
     * Poster
     * @var string
     */
    protected $poster;

    /**
     * Poster size
     * @var array
     */
    protected $posterSize;

    /**
     * Lightbox
     * @var bool
     */
    protected $lightbox = false;

    /**
     * Lightbox size
     * @var array
     */
    protected $lightboxSize = [];

    /**
     * Lightbox autoplay
     * @var bool
     */
    protected $lightboxAutoplay = false;

    /**
     * Images folder
     * @var string
     */
    protected $imagesFolder = 'system/vimeo/images';

    /**
     * VimeoVideo constructor.
     *
     * @param string     $id
     * @param array      $data
     * @param VideoCache $cache
     */
    public function __construct($id, array $data, VideoCache $cache)
    {
        $this->id    = $id;
        $this->data  = $data;
        $this->cache = $cache;
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
     * Set the albums data
     *
     * @param array $albums
     */
    public function setAlbums(array $albums)
    {
        $this->albums = $albums;
    }

    /**
     * Set the poster
     *
     * @param string $path
     */
    public function setPoster($path)
    {
        if (!is_file(TL_ROOT . '/' . $path)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist', $path));
        }

        $this->poster = $path;
    }

    /**
     * Get the poster
     *
     * @return string
     */
    public function getPoster()
    {
        if ($this->poster === null) {
            $this->poster = $this->downloadPoster();
        }

        return $this->poster;
    }

    /**
     * Set the poster size
     *
     * @param array $size
     */
    public function setPosterSize(array $size)
    {
        $this->posterSize = $size;
    }

    /**
     * Enable the lightbox
     */
    public function enableLightbox()
    {
        $this->lightbox = true;
    }

    /**
     * Disable the lightbox
     */
    public function disableLightbox()
    {
        $this->lightbox = false;
    }

    /**
     * Enable the lightbox autoplay
     */
    public function enableLightboxAutoplay()
    {
        $this->lightboxAutoplay = true;
    }

    /**
     * Disable the lightbox autoplay
     */
    public function disableLightboxAutoplay()
    {
        $this->lightboxAutoplay = true;
    }

    /**
     * Set the lightbox size
     *
     * @param array $lightboxSize
     */
    public function setLightboxSize(array $lightboxSize)
    {
        $this->lightboxSize = $lightboxSize;
    }

    /**
     * Generate the video
     *
     * @param FrontendTemplate $template
     *
     * @return string
     */
    public function generate(FrontendTemplate $template)
    {
        $this->addToTemplate($template);

        return $template->parse();
    }

    /**
     * Add video data to the template
     *
     * @param FrontendTemplate $template
     */
    public function addToTemplate(FrontendTemplate $template)
    {
        $template->setData($this->data);
        $template->id = $this->id;

        // Add lightbox features
        if ($this->lightbox) {
            $template->lightbox         = true;
            $template->lightboxAutoplay = $this->lightboxAutoplay ? true : false;
            $template->lightboxSize     = $this->lightboxSize;
        }

        $posterHelper = new \stdClass();
        Controller::addImageToTemplate($posterHelper, [
            'singleSRC' => $this->getPoster(),
            'size'      => $this->posterSize,
        ]);

        $template->albums = $this->albums;
        $template->poster = $posterHelper;
    }

    /**
     * Download the poster and return the local path to it
     *
     * @return string
     */
    protected function downloadPoster()
    {
        if (!is_array($this->data['pictures']['sizes'])) {
            return '';
        }

        $cacheKey = 'video_' . $this->id;

        // Get the image if it's not cached
        if (!$this->cache->hasImage($cacheKey)) {
            $picture = array_pop($this->data['pictures']['sizes']);
            $request = new Request();
            $request->send($picture['link']);

            if ($request->hasError()) {
                System::log(sprintf('Unable to download Vimeo video image "%s"', $picture['link']), __METHOD__, TL_ERROR);

                return '';
            }

            // Store the video image in the cache
            $this->cache->setImage($cacheKey, $request->response);
        }

        return $this->cache->getImage($cacheKey);
    }
}