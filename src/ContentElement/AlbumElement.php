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

namespace Derhaeuptling\VimeoApi\ContentElement;

use Contao\Config;
use Contao\ContentElement;
use Contao\FrontendTemplate;
use Derhaeuptling\VimeoApi\VimeoApi;
use Derhaeuptling\VimeoApi\VideoCache;
use Derhaeuptling\VimeoApi\VimeoVideo;

class AlbumElement extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_vimeo_album';

    /**
     * Extend the parent method
     *
     * @return string
     */
    public function generate()
    {
        if ($this->vimeo_albumId == '') {
            return '';
        }

        if (TL_MODE == 'BE') {
            return '<p><a href="https://vimeo.com/album/' . $this->vimeo_albumId . '" target="_blank">https://vimeo.com/album/' . $this->vimeo_albumId . '</a></p>';
        }

        return parent::generate();
    }

    /**
     * Generate the content element
     */
    protected function compile()
    {
        $api    = new VimeoApi(new VideoCache());
        $client = $api->getClient();

        // Get the album data
        $album = $api->getAlbum($client, $this->vimeo_albumId);
        $this->Template->setData($album);

        $posterSize = deserialize($this->size, true);
        $videos     = [];

        // Generate the videos
        /** @var VimeoVideo $video */
        foreach ($api->getAlbumVideos($client, $this->vimeo_albumId) as $video) {
            // Fetch the image data
            if (($image = $api->getVideoImage($client, $video->getId(), Config::get('vimeo_imageIndex'))) !== null) {
                $video->setPicturesData($image);
            }

            $video->setPosterSize($posterSize);

            // Enable the lightbox
            if ($this->vimeo_lightbox) {
                $video->enableLightbox();

                // Enable the lightbox autoplay
                if ($this->vimeo_lightboxAutoplay) {
                    $video->enableLightboxAutoplay();
                }
            }

            $videos[] = $video->generate(new FrontendTemplate($this->vimeo_template));
        }

        $this->Template->videos = $videos;
    }
}