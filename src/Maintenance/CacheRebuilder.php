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
     * Popup mode
     *
     * @var bool
     */
    protected $popup = false;

    /**
     * Set the popup mode
     *
     * @param boolean $popup
     */
    public function setPopupMode($popup)
    {
        $this->popup = (bool)$popup;
    }

    /**
     * Return true if the module is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (Input::get('act') === 'vimeo') || $this->popup;
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

            foreach ($elementsData as $data) {
                $elements[] = [
                    'type' => $GLOBALS['TL_LANG']['CTE'][$data['type']][0],
                    'ref'  => ($data['type'] === 'vimeo_album') ? $data['vimeo_albumId'] : $data['vimeo_videoId'],
                    'id'   => $data['id'],
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
        return Database::getInstance()->execute("SELECT id, type, vimeo_albumId, vimeo_videoId FROM tl_content WHERE type='vimeo_album' OR type='vimeo_video'")
            ->fetchAllAssoc();
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
                if ($api->getAlbum($client, $contentElement->vimeo_albumId) === null ||
                    count($api->getAlbumVideos($client, $contentElement->vimeo_albumId)) < 1
                ) {
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }
                break;

            case 'vimeo_video':
                if ($api->getVideo($client, $contentElement->vimeo_videoId) === null) {
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }
                break;
        }

        header('HTTP/1.1 200 OK');
        die('OK');
    }
}