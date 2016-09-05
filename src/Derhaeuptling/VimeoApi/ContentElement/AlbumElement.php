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
use Derhaeuptling\VimeoApi\Cache\Cache;
use Derhaeuptling\VimeoApi\Client;
use Derhaeuptling\VimeoApi\DataProvider\StandardProvider;
use Derhaeuptling\VimeoApi\Factory;
use Derhaeuptling\VimeoApi\Video;

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

        if (TL_MODE === 'BE') {
            return '<p><a href="https://vimeo.com/album/'.$this->vimeo_albumId.'" target="_blank">https://vimeo.com/album/'.$this->vimeo_albumId.'</a></p>';
        }

        return parent::generate();
    }

    /**
     * Generate the content element
     */
    protected function compile()
    {
        $dataProvider = new StandardProvider(new Cache(), Client::getInstance());
        $factory      = new Factory($dataProvider);
        $album        = $factory->createAlbum(
            $this->vimeo_albumId,
            true,
            $this->vimeo_sorting,
            $this->vimeo_sortingDirection
        );

        if ($album === null) {
            return;
        }

        $this->Template->setData($album->getData());
        $posterSize = deserialize($this->size, true);
        $videos     = [];

        // Generate the videos
        /** @var Video $video */
        foreach ($album->getVideos() as $video) {
            // Set the images
            if (Config::get('vimeo_allImages') && ($images = $dataProvider->getVideoImages($video->getId())) !== null) {
                $video->setPicturesData($images);
            }

            $image = $dataProvider->getVideoImage($video->getId(), Config::get('vimeo_imageIndex'));

            // Set the poster
            if ($image !== null) {
                $video->setPoster($image);
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