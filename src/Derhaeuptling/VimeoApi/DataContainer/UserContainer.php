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

use Contao\BackendTemplate;
use Contao\System;
use Derhaeuptling\VimeoApi\Cache\Rebuilder;

class UserContainer
{
    /**
     * Generate the purge cache field
     *
     * @return string
     */
    public function generatePurgeField()
    {
        System::loadLanguageFile('tl_maintenance');

        $template                = new BackendTemplate('be_vimeo_rebuilder_user');
        $template->elementsCount = count(Rebuilder::getContentElements());

        if (($stats = Rebuilder::generateStats()) !== null) {
            foreach ($stats as $k => $v) {
                $template->$k = $v;
            }
        }

        return $template->parse();
    }
}