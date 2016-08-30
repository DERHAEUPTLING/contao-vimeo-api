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
 * Models
 */
$GLOBALS['TL_MODELS']['tl_vimeo_cache'] = 'Derhaeuptling\VimeoApi\Model\CacheModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][] = ['Derhaeuptling\VimeoApi\Cache\Rebuilder', 'rebuild'];

/**
 * Add the Vimeo purge job
 */
$GLOBALS['TL_MAINTENANCE'][] = 'Derhaeuptling\VimeoApi\Cache\Rebuilder';

$GLOBALS['TL_PURGE']['folders']['vimeo'] = [
    'callback' => ['Derhaeuptling\VimeoApi\Cache\Cache', 'purge'],
    'affected' => [\Derhaeuptling\VimeoApi\Cache\Cache::$imagesFolder],
];

/**
 * Eligible content element types for Vimeo cache rebuild
 */
$GLOBALS['VIMEO_CACHE_REBUILDER']['vimeo_album'] = 'Derhaeuptling\VimeoApi\Cache\Handler\AlbumHandler';
$GLOBALS['VIMEO_CACHE_REBUILDER']['vimeo_video'] = 'Derhaeuptling\VimeoApi\Cache\Handler\VideoHandler';

/**
 * Set the default image index for Vimeo
 */
if (!$GLOBALS['TL_CONFIG']['vimeo_imageIndex']) {
    $GLOBALS['TL_CONFIG']['vimeo_imageIndex'] = 1;
}