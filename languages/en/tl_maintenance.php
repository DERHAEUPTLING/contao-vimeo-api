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
 * Maintenance
 */
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.headline']      = 'Rebuild the Vimeo cache';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.submit']        = 'Rebuild the cache';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.empty']         = 'There are no records to be rebuilt.';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.name']          = 'Name';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.value']         = 'Value';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.elementsLabel'] = 'Elements to be rebuilt';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.elements']      = '%s elements';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.limitLabel']    = 'Current limit';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.resetLabel']    = 'Limit reset time';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.limitRequests'] = '%s / %s requests';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.loading']       = 'Please wait while the Vimeo cache is being rebuilt.';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.complete']      = 'The Vimeo cache has been rebuilt. You can now proceed.';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableStatus']   = 'Status';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableType']     = 'Type';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableRef']      = 'Reference ID';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableSource']   = 'Source';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.recordLoading'] = 'Loading...';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.recordSuccess'] = 'Success';
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.recordError']   = 'Error';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_maintenance']['vimeo.tableSourceRef'] = [
    'article' => 'Article',
    'news'    => 'News',
    'event'   => 'Event',
];

/**
 * Maintenance jobs
 */
$GLOBALS['TL_LANG']['tl_maintenance_jobs']['vimeo'] = [
    'Purge the Vimeo cache',
    sprintf(
        'Removes the cached Vimeo video data and image files (%s entries).',
        \Derhaeuptling\VimeoApi\Model\CacheModel::countAll()
    ),
];
