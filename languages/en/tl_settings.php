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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_settings']['vimeo_clientId']     = ['Vimeo client ID', 'Please enter the Vimeo client ID.'];
$GLOBALS['TL_LANG']['tl_settings']['vimeo_clientSecret'] = [
    'Vimeo client secret',
    'Please enter the Vimeo client secret.',
];
$GLOBALS['TL_LANG']['tl_settings']['vimeo_accessToken']  = [
    'Vimeo access token',
    'Please enter the Vimeo access token (it can be generated in your Vimeo app settings).',
];
$GLOBALS['TL_LANG']['tl_settings']['vimeo_imageIndex']   = [
    'Vimeo image index',
    'Here you can enter the Vimeo image index to be downloaded. For example, if you enter 2 the second image will be downloaded instead of the first one (default).',
];
$GLOBALS['TL_LANG']['tl_settings']['vimeo_allImages']    = [
    'Get all images data',
    'Get all images data of every video. This is useful when you want to display e.g. multiple thumbnails but it affects the performance.',
];

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_settings']['vimeo_explanation'] = '<div class="tl_info" style="margin-top:10px;">You have to create the Vimeo app at <a href="https://developer.vimeo.com/apps/" target="_blank">https://developer.vimeo.com/apps/</a> and generate the personal access token.</div>';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_settings']['vimeo_legend'] = 'Vimeo settings';
