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

class Video
{
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
     * Album
     * @var Album
     */
    protected $album;

    /**
     * Custom name
     * @var string
     */
    protected $customName;

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
     * Link
     * @var bool
     */
    protected $link = false;

    /**
     * Link URL
     * @var string
     */
    protected $linkUrl;

    /**
     * Link title
     * @var string
     */
    protected $linkTitle;

    /**
     * Video constructor.
     *
     * @param string $id
     * @param array  $data
     */
    public function __construct($id, array $data)
    {
        $this->id   = $id;
        $this->data = $data;
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
     * Set the album
     *
     * @param Album $album
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;
    }

    /**
     * Set the poster
     *
     * @param string $path
     */
    public function setPoster($path)
    {
        if (!is_file(TL_ROOT.'/'.$path)) {
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
     * Set the pictures data
     *
     * @param array $data
     */
    public function setPicturesData(array $data)
    {
        $this->data['pictures']['sizes'] = $data;
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
     * Enable the link
     */
    public function enableLink()
    {
        $this->link = true;
    }

    /**
     * Disable the link
     */
    public function disableLink()
    {
        $this->link = false;
    }

    /**
     * Get the link title
     *
     * @return string
     */
    public function getLinkTitle()
    {
        return $this->linkTitle;
    }

    /**
     * Set the link title
     *
     * @param string $linkTitle
     */
    public function setLinkTitle($linkTitle)
    {
        $this->linkTitle = $linkTitle;
    }

    /**
     * Get the link URL
     *
     * @return string
     */
    public function getLinkUrl()
    {
        return $this->linkUrl;
    }

    /**
     * Set the link URL
     *
     * @param string $linkUrl
     */
    public function setLinkUrl($linkUrl)
    {
        $this->linkUrl = $linkUrl;
    }

    /**
     * Get the custom name
     *
     * @return string
     */
    public function getCustomName()
    {
        return $this->customName;
    }

    /**
     * Set the custom name
     *
     * @param string $customName
     */
    public function setCustomName($customName)
    {
        $this->customName = $customName;
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

        // Add link features
        if ($this->link) {
            $template->internalLink = true;
            $template->linkUrl      = $this->linkUrl;
            $template->linkTitle    = $this->linkTitle;
        }

        // Add poster
        if ($this->poster !== null) {
            $posterHelper = new \stdClass();

            Controller::addImageToTemplate(
                $posterHelper,
                [
                    'singleSRC' => $this->poster,
                    'size'      => $this->posterSize,
                ]
            );

            $template->poster = $posterHelper;
        }

        $template->customName = $this->customName;
        $template->album      = ($this->album !== null) ? $this->album->getData() : [];
    }
}