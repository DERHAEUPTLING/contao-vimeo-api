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

use Contao\ContentElement;
use Contao\FrontendTemplate;
use Derhaeuptling\VimeoApi\VimeoApi;
use Derhaeuptling\VimeoApi\VideoCache;

class VideoElement extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_vimeo_video';

    /**
     * Extend the parent method
     *
     * @return string
     */
    public function generate()
    {
        if ($this->vimeo_videoId == '') {
            return '';
        }

        // Generate the backend buffer
        if (TL_MODE == 'BE') {
            $buffer = '<p><a href="https://vimeo.com/' . $this->vimeo_videoId . '" target="_blank">https://vimeo.com/' . $this->vimeo_videoId . '</a>';

            // Display the hint that the video is linked to URL
            if ($this->vimeo_link) {
                $buffer .= ' - '.sprintf($GLOBALS['TL_LANG']['MSC']['vimeo.video_link'], $this->url);
            }

            $buffer .= '</p>';

            // Display the video image
            if (($video = $this->getVideo()) !== null) {
                $buffer .= '<figure><img src="'.$video->getPoster().'" width="160" height="" alt=""></figure>';
            }

            return $buffer;
        }

        return parent::generate();
    }

    /**
     * Generate the content element
     */
    protected function compile()
    {
        if (($video = $this->getVideo()) === null) {
            return;
        }

        $video->setCustomName($this->vimeo_customName);
        $video->setPosterSize(deserialize($this->size, true));

        // Enable the lightbox
        if ($this->vimeo_lightbox) {
            $video->enableLightbox();

            // Enable the lightbox autoplay
            if ($this->vimeo_lightboxAutoplay) {
                $video->enableLightboxAutoplay();
            }
        }

        // Enable the link
        if ($this->vimeo_link) {
            $video->enableLink();
            $video->setLinkUrl($this->url);
            $video->setLinkTitle($this->titleText);
        }

        // Set a custom poster
        if ($this->vimeo_customPoster) {
            $fileModel = \FilesModel::findByPk($this->singleSRC);

            if ($fileModel !== null && is_file(TL_ROOT . '/' . $fileModel->path)) {
                $video->setPoster($fileModel->path);
            }
        }

        $this->Template->buffer = $video->generate(new FrontendTemplate($this->vimeo_template));
    }

    /**
     * Get the video
     *
     * @return \Derhaeuptling\VimeoApi\VimeoVideo|null
     */
    protected function getVideo()
    {
        $api = new VimeoApi(new VideoCache());

        return $api->getVideo($api->getClient(), $this->vimeo_videoId);
    }
}