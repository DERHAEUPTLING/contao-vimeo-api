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

class UserDataContainer
{
    /**
     * Generate the purge cache field
     *
     * @return string
     */
    public function generatePurgeField()
    {
        return '<div class="w50">
  <h3><label for="ctrl_name">'.$GLOBALS['TL_LANG']['tl_user']['vimeoRebuildLabel'][0].'</label></h3>
  <a href="system/modules/vimeo_api/public/rebuild.php" class="tl_submit" style="margin:5px 0;" onclick="Backend.openModalIframe({\'width\':768,\'title\':\''.specialchars($GLOBALS['TL_LANG']['tl_user']['vimeoRebuildLabel'][0]).'\',\'url\':this.href});return false">'.specialchars($GLOBALS['TL_LANG']['tl_user']['vimeoRebuildButton']).'</a>
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_user']['vimeoRebuildLabel'][1].'</p>
</div>';
    }
}