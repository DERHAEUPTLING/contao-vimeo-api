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

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['media']['vimeo_album'] = 'Derhaeuptling\VimeoApi\ContentElement\AlbumElement';
$GLOBALS['TL_CTE']['media']['vimeo_video'] = 'Derhaeuptling\VimeoApi\ContentElement\VideoElement';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][] = ['Derhaeuptling\VimeoApi\Maintenance\CacheRebuilder', 'rebuildCache'];

/**
 * Add the Vimeo purge job
 */
$GLOBALS['TL_MAINTENANCE'][] = 'Derhaeuptling\VimeoApi\Maintenance\CacheRebuilder';

$GLOBALS['TL_PURGE']['folders']['vimeo'] = [
    'callback' => ['Derhaeuptling\VimeoApi\VideoCache', 'purge'],
    'affected' => [\Derhaeuptling\VimeoApi\VideoCache::getRootFolder()],
];

/**
 * Set the default image index for Vimeo
 */
if (!$GLOBALS['TL_CONFIG']['vimeo_imageIndex']) {
    $GLOBALS['TL_CONFIG']['vimeo_imageIndex'] = 1;
}