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

use Contao\Automator;
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
                switch ($data['ptable']) {
                    case 'tl_article':
                        $source = $GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableSourceRef']['article'];
                        $path   = [$data['page'], $data['article']];
                        break;

                    case 'tl_news':
                        $source = $GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableSourceRef']['news'];
                        $path   = [$data['archive'], $data['news']];
                        break;

                    case 'tl_calendar_events':
                        $source = $GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableSourceRef']['event'];
                        $path   = [$data['calendar'], $data['event']];
                        break;

                    default:
                        $source = '';
                        $path   = [];
                }

                $elements[] = [
                    'type'   => $data['type'],
                    'ref'    => ($data['type'] === 'vimeo_album') ? $data['vimeo_albumId'] : $data['vimeo_videoId'],
                    'id'     => $data['id'],
                    'source' => $source,
                    'path'   => $path,
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
        return Database::getInstance()->execute("
SELECT
tl_content.id, tl_content.type, tl_content.vimeo_albumId, tl_content.vimeo_videoId, tl_content.ptable,
tl_article.title AS article,
tl_page.title AS page,
tl_news.headline AS news,
tl_news_archive.title AS archive,
tl_calendar_events.title AS event,
tl_calendar.title AS calendar
FROM tl_content
LEFT JOIN tl_article ON tl_article.id=tl_content.pid AND tl_content.ptable='tl_article'
LEFT JOIN tl_page ON tl_page.id=tl_article.pid
LEFT JOIN tl_news ON tl_news.id=tl_content.pid AND tl_content.ptable='tl_news'
LEFT JOIN tl_news_archive ON tl_news_archive.id=tl_news.pid
LEFT JOIN tl_calendar_events ON tl_calendar_events.id=tl_content.pid AND tl_content.ptable='tl_calendar_events'
LEFT JOIN tl_calendar ON tl_calendar.id=tl_calendar_events.pid
WHERE tl_content.type='vimeo_album' OR tl_content.type='vimeo_video'")
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

        switch (Input::post('cache')) {
            case 'page':
                $this->rebuildPageCache();
                break;

            case 'vimeo':
                $this->rebuildVimeoCache();
                break;
        }
    }

    /**
     * Rebuild the page cache
     */
    protected function rebuildPageCache()
    {
        $automator = new Automator();
        $automator->purgePageCache();
    }

    /**
     * Rebuild the Vimeo cache
     */
    protected function rebuildVimeoCache()
    {
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