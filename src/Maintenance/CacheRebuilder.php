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

namespace Derhaeuptling\VimeoApi\Maintenance;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use Derhaeuptling\VimeoApi\VimeoApi;

class CacheRebuilder implements \executable
{
    /**
     * Ajax action name
     * @var string
     */
    protected $ajaxAction = 'vimeo_api_rebuild_cache';

    /**
     * Return true if the module is active
     *
     * @return bool
     */
    public function isActive()
    {
        return Input::get('act') === 'vimeo';
    }

    /**
     * Generate the module
     *
     * @return string
     */
    public function run()
    {
        $GLOBALS['TL_CSS'][] = 'system/modules/vimeo_api/assets/backend.min.css';

        $elementsData = $this->getContentElements();
        $template     = new BackendTemplate('be_vimeo_rebuilder');

        $template->action        = ampersand(Environment::get('request'));
        $template->isActive      = $this->isActive();
        $template->elementsCount = count($elementsData);

        // Generate the elements
        if ($this->isActive()) {
            $elements = [];

            foreach ($elementsData as $id => $type) {
                $elements[] = [
                    'type' => $GLOBALS['TL_LANG']['CTE'][$type][0],
                    'id'   => $id,
                ];
            }

            $template->elements   = $elements;
            $template->ajaxAction = $this->ajaxAction;
        }

        return $template->parse();
    }

    /**
     * Get the content elements
     *
     * @return array
     */
    protected function getContentElements()
    {
        $return   = [];
        $elements = Database::getInstance()->execute("SELECT id, type FROM tl_content WHERE type='vimeo_album' OR type='vimeo_video'");

        while ($elements->next()) {
            $return[$elements->id] = $elements->type;
        }

        return $return;
    }

    /**
     * Handle the AJAX request and rebuild the cache
     *
     * @param string $action
     */
    public function rebuildCache($action)
    {
        if ($action !== $this->ajaxAction) {
            return;
        }

        // Throw an error if content element could not be found
        if (($contentElement = ContentModel::findByPk(Input::post('id'))) === null) {
            System::log(sprintf('Unable to find the content element ID %s', Input::post('id')), __METHOD__, TL_ERROR);
            header('HTTP/1.1 400 Bad Request');
            die('Bad Request');
        }

        $api    = new VimeoApi(new ClearCache());
        $client = $api->getClient();

        switch ($contentElement->type) {
            case 'vimeo_album':
                $api->getAlbum($client, $contentElement->vimeo_albumId);
                $api->getAlbumVideos($client, $contentElement->vimeo_albumId);
                break;

            case 'vimeo_video':
                $api->getVideo($client, $contentElement->vimeo_videoId);
                break;
        }

        header('HTTP/1.1 200 OK');
        die('OK');
    }
}