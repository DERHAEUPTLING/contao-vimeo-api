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
     * Generate the content element
     */
    protected function compile()
    {
        $api = new VimeoApi(new VideoCache());
        $video = $api->getVideo($api->getClient(), $this->vimeo_videoId);

        if ($video === null) {
            return;
        }

        $video->setSize(deserialize($this->playerSize, true));
        $video->setPosterSize(deserialize($this->size, true));

        // Enable the lightbox
        if ($this->vimeo_lightbox) {
            $video->enableLightbox();
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
}