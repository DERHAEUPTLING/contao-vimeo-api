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

namespace Derhaeuptling\VimeoApi\Maintenance;

use BackendTemplate;
use Contao\Ajax;
use Contao\Backend;
use Contao\Config;
use Contao\Environment;
use Contao\Input;
use Contao\System;

class CacheRebuilderPopup extends Backend
{
    /**
     * Current Ajax object
     * @var Ajax
     */
    protected $ajax;

    /**
     * Initialize the controller
     *
     * 1. Import the user
     * 2. Call the parent constructor
     * 3. Authenticate the user
     * 4. Load the language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        $this->import('BackendUser', 'User');
        parent::__construct();

        $this->User->authenticate();
        System::loadLanguageFile('default');
        System::loadLanguageFile('tl_maintenance');
    }

    /**
     * Run the controller and parse the template
     */
    public function run()
    {
        $template       = new BackendTemplate('be_picker');
        $template->main = '';

        // Ajax request
        if ($_POST && Environment::get('isAjaxRequest')) {
            $this->ajax = new Ajax(Input::post('action'));
            $this->ajax->executePreActions();
        }

        $rebuilder = new CacheRebuilder();
        $rebuilder->setPopupMode(true);

        $template->main        = $rebuilder->run();
        $template->theme       = Backend::getTheme();
        $template->base        = Environment::get('base');
        $template->language    = $GLOBALS['TL_LANGUAGE'];
        $template->title       = specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']);
        $template->charset     = Config::get('characterSet');

        Config::set('debugMode', false);

        $template->output();
    }
}