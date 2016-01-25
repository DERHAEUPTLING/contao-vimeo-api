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

namespace Derhaeuptling\VimeoApi;

use Contao\File;
use Contao\Folder;
use Contao\System;

class VideoCache
{
    /**
     * Folder path
     * @var string
     */
    protected static $rootFolder = 'system/vimeo';

    /**
     * Data folder path
     * @var string
     */
    protected $dataFolder = 'system/vimeo/data';

    /**
     * Images folder path
     * @var string
     */
    protected $imagesFolder = 'system/vimeo/images';

    /**
     * Get the root folder
     *
     * @return string
     */
    public static function getRootFolder()
    {
        return static::$rootFolder;
    }

    /**
     * Purge the data
     */
    public function purge()
    {
        $folder = new \Folder(static::$rootFolder);
        $folder->purge();

        // Add a log entry
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
        $data = $this->getData($key);

        if ($data === null) {
            return false;
        }

        return true;
    }

    /**
     * Get the video data
     *
     * @param string $key
     *
     * @return array|null
     */
    public function getData($key)
    {
        return $this->readDataFile($this->getDataFilePath($key));
    }

    /**
     * Set the video data
     *
     * @param string $key
     * @param array  $data
     */
    public function setData($key, $data)
    {
        $this->writeDataFile($this->getDataFilePath($key), $data);
    }

    /**
     * Get the data cache file path
     *
     * @param string $key
     *
     * @return string
     */
    protected function getDataFilePath($key)
    {
        return $this->dataFolder . '/' . $key . '.json';
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
        $data = $this->getImage($key);

        if ($data === null) {
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

        if (!is_file(TL_ROOT . '/' . $filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * Set the video image
     *
     * @param string $key
     * @param string $content
     */
    public function setImage($key, $content)
    {
        $folder = new Folder($this->imagesFolder);

        // Create the .htaccess file so images are accessible
        if (!is_file(TL_ROOT . '/' . $folder->path . '/.htaccess')) {
            $htaccessFile = new File($folder->path . '/.htaccess');
            $htaccessFile->write("<IfModule !mod_authz_core.c>\r\nOrder allow,deny\r\nAllow from all\r\n</IfModule>\r\n<IfModule mod_authz_core.c>\r\n  Require all granted\r\n</IfModule>");
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
        return $this->imagesFolder . '/' . $key . '.jpg';
    }

    /**
     * Write the data file
     *
     * @param string $path
     * @param array  $data
     */
    protected function writeDataFile($path, array $data)
    {
        $file = new \File($path);
        $file->truncate();
        $file->write(json_encode($data));
        $file->close();
    }

    /**
     * Read the data file
     *
     * @param string $path
     *
     * @return array|null
     */
    protected function readDataFile($path)
    {
        if (!is_file(TL_ROOT . '/' . $path)) {
            return null;
        }

        $file = new \File($path, true);

        return json_decode($file->getContent(), true);
    }
}