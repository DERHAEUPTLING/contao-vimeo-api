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

namespace Derhaeuptling\VimeoApi\DataContainer;

use Contao\ContentModel;
use Contao\DataContainer;
use Contao\Message;
use Contao\System;
use Derhaeuptling\VimeoApi\Cache\Rebuilder;

class ContentContainer
{
    /**
     * Rebuild Vimeo cache
     *
     * @param DataContainer $dc
     */
    public function rebuildVimeoCache(DataContainer $dc)
    {
        $rebuilder = new Rebuilder();

        try {
            $result = $rebuilder->rebuildElementCache(ContentModel::findByPk($dc->id));
        } catch (\InvalidArgumentException $e) {
            return;
        } catch (\RuntimeException $e) {
            System::log(
                sprintf('Unable to rebuild Vimeo cache of element ID %s: %s', $dc->id, $e->getMessage()),
                __METHOD__,
                TL_ERROR
            );

            $result = false;
        }

        if ($result === true) {
            Message::addConfirmation($GLOBALS['TL_LANG']['tl_content']['vimeo_cacheConfirm']);
        } elseif ($result === false) {
            Message::addError($GLOBALS['TL_LANG']['tl_content']['vimeo_cacheError']);
        }
    }
}