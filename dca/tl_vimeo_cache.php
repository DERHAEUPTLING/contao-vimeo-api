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
 * Table tl_vimeo_cache
 */
$GLOBALS['TL_DCA']['tl_vimeo_cache'] = [

    // Config
    'config' => [
        'dataContainer' => 'Table',
        'sql'           => [
            'keys' => [
                'id'  => 'primary',
                'uid' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'uid'       => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'reference' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'data'      => [
            'sql' => "blob NULL",
        ],
        'obsolete'  => [
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];