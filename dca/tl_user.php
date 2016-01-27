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
 * Replace the "session" field callback
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['session']['input_field_callback'] = ['Derhaeuptling\VimeoApi\UserDataContainer', 'generateSessionField'];
