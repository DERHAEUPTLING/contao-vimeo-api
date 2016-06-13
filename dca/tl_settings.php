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
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{vimeo_legend},vimeo_explanation,vimeo_clientId,vimeo_clientSecret,vimeo_accessToken,vimeo_imageIndex';

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['vimeo_explanation'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['vimeo_explanation'],
    'exclude'                 => true,
    'input_field_callback'    => function () {
        return $GLOBALS['TL_LANG']['tl_settings']['vimeo_explanation'];
    }
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['vimeo_clientId'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['vimeo_clientId'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['vimeo_clientSecret'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['vimeo_clientSecret'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['vimeo_accessToken'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['vimeo_accessToken'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['vimeo_imageIndex'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['vimeo_imageIndex'],
    'default'                 => 1,
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'digit', 'minval'=>1, 'tl_class'=>'w50'),
];