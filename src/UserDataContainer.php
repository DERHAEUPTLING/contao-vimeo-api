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

use Contao\Environment;

class UserDataContainer
{
    /**
     * Return a checkbox to delete session data
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function generateSessionField(\DataContainer $dc)
    {
        if (\Input::post('FORM_SUBMIT') === 'tl_user') {
            $purge = \Input::post('purge');

            if (is_array($purge)) {
                $session = \Session::getInstance();
                $automator = new \Automator();

                if (in_array('purge_session', $purge, true)) {
                    $session->setData(array());
                    \Message::addConfirmation($GLOBALS['TL_LANG']['tl_user']['sessionPurged']);
                }

                if (in_array('purge_images', $purge, true)) {
                    $automator->purgeImageCache();
                    \Message::addConfirmation($GLOBALS['TL_LANG']['tl_user']['htmlPurged']);
                }

                if (in_array('purge_pages', $purge, true)) {
                    $automator->purgePageCache();
                    \Message::addConfirmation($GLOBALS['TL_LANG']['tl_user']['tempPurged']);
                }

                if (in_array('purge_vimeo', $purge, true)) {
                    $cache = new VideoCache();
                    $cache->purge();

                    \Message::addConfirmation($GLOBALS['TL_LANG']['tl_user']['vimeoPurged']);
                }
            }
        }

        return '
<div>
  <fieldset class="tl_checkbox_container">
    <legend>' . $GLOBALS['TL_LANG']['tl_user']['session'][0] . '</legend>
    <input type="checkbox" id="check_all_purge" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this, \'ctrl_purge\')"> <label for="check_all_purge" style="color:#a6a6a6"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>
    <input type="checkbox" name="purge[]" id="opt_purge_0" class="tl_checkbox" value="purge_session" onfocus="Backend.getScrollOffset()"> <label for="opt_purge_0">' . $GLOBALS['TL_LANG']['tl_user']['sessionLabel'] . '</label><br>
    <input type="checkbox" name="purge[]" id="opt_purge_1" class="tl_checkbox" value="purge_images" onfocus="Backend.getScrollOffset()"> <label for="opt_purge_1">' . $GLOBALS['TL_LANG']['tl_user']['htmlLabel'] . '</label><br>
    <input type="checkbox" name="purge[]" id="opt_purge_2" class="tl_checkbox" value="purge_pages" onfocus="Backend.getScrollOffset()"> <label for="opt_purge_2">' . $GLOBALS['TL_LANG']['tl_user']['tempLabel'] . '</label><br>
    <input type="checkbox" name="purge[]" id="opt_purge_3" class="tl_checkbox" value="purge_vimeo" onfocus="Backend.getScrollOffset()"> <label for="opt_purge_3">' . $GLOBALS['TL_LANG']['tl_user']['vimeoLabel'] . '</label>
  </fieldset>' . $dc->help() . '
</div>';
    }

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