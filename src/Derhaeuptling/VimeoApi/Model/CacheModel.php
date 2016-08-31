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

namespace Derhaeuptling\VimeoApi\Model;

class CacheModel extends \Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_vimeo_cache';

    /**
     * Return true if the record has reference
     *
     * @return bool
     */
    public function hasReference()
    {
        return $this->reference ? true : false;
    }

    /**
     * Get the record reference
     *
     * @return CacheModel|null
     */
    public function getReference()
    {
        if (!$this->hasReference()) {
            return null;
        }

        return static::findOneBy('uid', $this->reference);
    }
}