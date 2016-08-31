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
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Derhaeuptling\VimeoApi', 'system/modules/vimeo_api/src');

/**
 * Register the templates
 */
TemplateLoader::addFiles(
    [
        // Backend
        'be_vimeo_rebuilder'      => 'system/modules/vimeo_api/templates/backend',
        'be_vimeo_rebuilder_user' => 'system/modules/vimeo_api/templates/backend',

        // Content elements
        'ce_vimeo_album'          => 'system/modules/vimeo_api/templates/elements',
        'ce_vimeo_video'          => 'system/modules/vimeo_api/templates/elements',

        // Vimeo
        'vimeo_default'           => 'system/modules/vimeo_api/templates/vimeo',
    ]
);

/**
 * Register the Vimeo autoloader
 */
require_once TL_ROOT.'/system/modules/vimeo_api/vendor/vimeo/autoload.php';
