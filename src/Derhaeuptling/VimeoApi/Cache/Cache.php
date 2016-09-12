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

namespace Derhaeuptling\VimeoApi\Cache;

use Contao\Database;
use Contao\File;
use Contao\Files;
use Contao\Folder;
use Contao\System;
use Derhaeuptling\VimeoApi\Model\CacheModel;

class Cache
{
    /**
     * Images folder path
     * @var string
     */
    public static $imagesFolder = 'system/vimeo';

    /**
     * Purge the data
     */
    public function purge()
    {
        // Purge the data
        Database::getInstance()->query("TRUNCATE TABLE tl_vimeo_cache");

        // Purge the images
        $folder = new \Folder(static::$imagesFolder);
        $folder->purge();

        // Log the action
        System::log('Purged the Vimeo cache', __METHOD__, TL_CRON);
    }

    /**
     * Return true if there is data
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key)
    {
        if ($this->getData($key) === null) {
            return false;
        }

        return true;
    }

    /**
     * Get the data
     *
     * @param string $key
     *
     * @return array|null
     */
    public function getData($key)
    {
        if (($model = $this->getModel($key)) === null) {
            return null;
        }

        // Use the reference
        if (($reference = $model->getReference()) !== null) {
            $model = $reference;
        }

        return json_decode($model->data, true);
    }

    /**
     * Set the data
     *
     * @param string $key
     * @param array  $data
     */
    public function setData($key, array $data)
    {
        if (($model = $this->getModel($key)) === null) {
            $model = new CacheModel();
        }

        $model->tstamp   = time();
        $model->uid      = $key;
        $model->data     = json_encode($data);
        $model->obsolete = '';
        $model->save();
    }

    /**
     * Clear the data
     *
     * @param string $key
     */
    public function clearData($key)
    {
        if (($model = $this->getModel($key)) !== null) {
            $model->delete();
        }
    }

    /**
     * Set the reference
     *
     * @param string $key
     * @param string $reference
     *
     * @throws \InvalidArgumentException
     */
    public function setReference($key, $reference)
    {
        if ($this->getModel($reference) === null) {
            throw new \InvalidArgumentException(sprintf('The reference "%s" does not exist', $reference));
        }

        if (($model = $this->getModel($key)) === null) {
            $model = new CacheModel();
        }

        $model->tstamp    = time();
        $model->uid       = $key;
        $model->reference = $reference;
        $model->obsolete  = '';
        $model->save();
    }

    /**
     * Get the model
     *
     * @param string $key
     *
     * @return CacheModel|null
     */
    protected function getModel($key)
    {
        return CacheModel::findOneBy('uid', $key);
    }

    /**
     * Mark the whole data cache as obsolete
     */
    public function markAllObsolete()
    {
        Database::getInstance()->query("UPDATE tl_vimeo_cache SET obsolete=1");
    }

    /**
     * Return true if the data is obsolete
     *
     * @param string $key
     *
     * @return bool
     */
    public function isDataObsolete($key)
    {
        if (($model = $this->getModel($key)) === null) {
            return true;
        }

        return $model->obsolete ? true : false;
    }

    /**
     * Return true if there is image
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasImage($key)
    {
        if (($data = $this->getImage($key)) === null) {
            return false;
        }

        return true;
    }

    /**
     * Get the image
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getImage($key)
    {
        $filePath = $this->getImageFilePath($key);

        if (!is_file(TL_ROOT.'/'.$filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * Clear the image
     *
     * @param string $key
     */
    public function clearImage($key)
    {
        $filePath = $this->getImageFilePath($key);

        if (is_file(TL_ROOT.'/'.$filePath)) {
            Files::getInstance()->delete($filePath);
        }
    }

    /**
     * Set the video image
     *
     * @param string $key
     * @param string $content
     */
    public function setImage($key, $content)
    {
        $folder = new Folder(static::$imagesFolder);

        // Create the .htaccess file so images are accessible
        if (!is_file(TL_ROOT.'/'.$folder->path.'/.htaccess')) {
            $htaccessFile = new File($folder->path.'/.htaccess');
            $htaccessFile->write(
                "<IfModule !mod_authz_core.c>\r\nOrder allow,deny\r\nAllow from all\r\n</IfModule>\r\n<IfModule mod_authz_core.c>\r\n  Require all granted\r\n</IfModule>"
            );
            $htaccessFile->close();
        }

        $file = new \File($this->getImageFilePath($key));
        $file->truncate();
        $file->write($content);
        $file->close();
    }

    /**
     * Get the image cache file path
     *
     * @param string $key
     *
     * @return string
     */
    protected function getImageFilePath($key)
    {
        return static::$imagesFolder.'/'.$key.'.jpg';
    }
}