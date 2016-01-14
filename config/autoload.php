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
 * Register the namespace
 */
ClassLoader::addNamespace('Derhaeuptling\VimeoApi');

/**
 * Register the classes
 */
ClassLoader::addClasses([
    'Derhaeuptling\VimeoApi\VimeoApi'                    => 'system/modules/vimeo_api/src/VimeoApi.php',
    'Derhaeuptling\VimeoApi\VideoCache'                  => 'system/modules/vimeo_api/src/VideoCache.php',
    'Derhaeuptling\VimeoApi\VimeoVideo'                  => 'system/modules/vimeo_api/src/VimeoVideo.php',
    'Derhaeuptling\VimeoApi\ContentElement\VideoElement' => 'system/modules/vimeo_api/src/ContentElement/VideoElement.php',
]);

/**
 * Register the templates
 */
TemplateLoader::addFiles([
    // Content elements
    'ce_vimeo_video' => 'system/modules/vimeo_api/templates/elements',

    // Vimeo
    'vimeo_default'  => 'system/modules/vimeo_api/templates/vimeo',
]);

/**
 * Register the Vimeo autoloader
 */
require_once TL_ROOT . '/system/modules/vimeo_api/vendor/vimeo/autoload.php';
