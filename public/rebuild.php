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

// Set the script name
define('TL_SCRIPT', 'system/modules/vimeo-api/public/rebuild.php');

// Initialize the system
define('TL_MODE', 'BE');

// Include the Contao initialization script
if (file_exists('../../../initialize.php')) {
    // Regular way
    /** @noinspection PhpIncludeInspection */
    require_once '../../../initialize.php';
} elseif (file_exists('../../../../system/initialize.php')) {
    // Contao 4 - Try composer location
    /** @noinspection PhpIncludeInspection */
    require_once '../../../../system/initialize.php';
} else {
    // Contao 3 - Try composer location
    /** @noinspection PhpIncludeInspection */
    require_once '../../../../../system/initialize.php';
}

// Run the controller
$controller = new \Derhaeuptling\VimeoApi\Maintenance\RebuilderPopup();
$controller->run();
