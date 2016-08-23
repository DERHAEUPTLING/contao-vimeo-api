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
 * Add global callbacks
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = [
    'Derhaeuptling\VimeoApi\ContentContainer', 'rebuildVimeoCache'
];

/**
 * Add palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]        = 'vimeo_customPoster';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]        = 'vimeo_lightbox';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]        = 'vimeo_link';
$GLOBALS['TL_DCA']['tl_content']['palettes']['vimeo_album']           = '{type_legend},type,headline;{source_legend},vimeo_albumId,vimeo_lightbox,vimeo_sorting,vimeo_sortingDirection;{template_legend:hide},customTpl,vimeo_template,size;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['vimeo_video']           = '{type_legend},type,headline;{source_legend},vimeo_videoId,vimeo_customName,vimeo_lightbox,vimeo_link;{poster_legend:hide},vimeo_customPoster;{template_legend:hide},customTpl,vimeo_template,size;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['vimeo_customPoster'] = 'singleSRC';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['vimeo_lightbox']     = 'vimeo_lightboxAutoplay';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['vimeo_link']         = 'url,titleText';

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_albumId'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_albumId'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_videoId'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_videoId'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_customName'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_customName'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_lightbox'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_lightbox'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_lightbox'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_lightbox'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
    'sql'                     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_lightboxAutoplay'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_lightboxAutoplay'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'clr'),
    'sql'                     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_link'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_link'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
    'sql'                     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_customPoster'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_customPoster'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
    'sql'                     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_template'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_template'],
    'default'                 => 'vimeo_default',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => function () {
        return \Controller::getTemplateGroup('vimeo_');
    },
    'eval'                    => array('chosen'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_sorting'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_sorting'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => [
        'manual',
        'date',
        'alphabetical',
        'plays',
        'likes',
        'comments',
        'duration',
        'modified_time',
    ],
    'reference'               => &$GLOBALS['TL_LANG']['tl_content']['vimeo_sorting'],
    'eval'                    => array(
        'includeBlankOption' => true,
        'blankOptionLabel'   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_sorting']['blank'],
        'tl_class'           => 'w50',
    ),
    'sql'                     => "varchar(13) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['vimeo_sortingDirection'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_sortingDirection'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => ['asc', 'desc'],
    'reference'               => &$GLOBALS['TL_LANG']['tl_content']['vimeo_sortingDirection'],
    'eval'                    => array(
        'includeBlankOption' => true,
        'blankOptionLabel'   => &$GLOBALS['TL_LANG']['tl_content']['vimeo_sortingDirection']['blank'],
        'tl_class'           => 'w50',
    ),
    'sql'                     => "varchar(4) NOT NULL default ''"
];
