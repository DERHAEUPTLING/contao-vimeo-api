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
     * Return true if there is video data
     *
     * @param string $videoId
     *
     * @return bool
     */
    public function hasVideoData($videoId)
    {
        $data = $this->getVideoData($videoId);

        if ($data === null) {
            return false;
        }

        return true;
    }

    /**
     * Get the video data
     *
     * @param string $videoId
     *
     * @return array|null
     */
    public function getVideoData($videoId)
    {
        $filePath = $this->getDataFilePath($videoId);

        if (!is_file(TL_ROOT . '/' . $filePath)) {
            return null;
        }

        $file = new \File($filePath, true);

        return json_decode($file->getContent(), true);
    }

    /**
     * Get the data cache file path
     *
     * @param string $videoId
     *
     * @return string
     */
    protected function getDataFilePath($videoId)
    {
        return $this->dataFolder . '/' . $videoId . '.json';
    }

    /**
     * Set the video data
     *
     * @param string $videoId
     * @param array  $data
     */
    public function setVideoData($videoId, array $data)
    {
        $file = new \File($this->getDataFilePath($videoId));
        $file->truncate();
        $file->write(json_encode($data));
        $file->close();
    }

    /**
     * Return true if there is video image
     *
     * @param string $videoId
     *
     * @return bool
     */
    public function hasVideoImage($videoId)
    {
        $data = $this->getVideoImage($videoId);

        if ($data === null) {
            return false;
        }

        return true;
    }

    /**
     * Get the video image
     *
     * @param string $videoId
     *
     * @return string|null
     */
    public function getVideoImage($videoId)
    {
        $filePath = $this->getImageFilePath($videoId);

        if (!is_file(TL_ROOT . '/' . $filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * Set the video image
     *
     * @param string $videoId
     * @param string $content
     */
    public function setVideoImage($videoId, $content)
    {
        $folder = new Folder($this->imagesFolder);

        // Create the .htaccess file so images are accessible
        if (!is_file(TL_ROOT . '/' . $folder->path . '/.htaccess')) {
            $htaccessFile = new File($folder->path . '/.htaccess');
            $htaccessFile->write("<IfModule !mod_authz_core.c>\r\nOrder allow,deny\r\nAllow from all\r\n</IfModule>\r\n<IfModule mod_authz_core.c>\r\n  Require all granted\r\n</IfModule>");
            $htaccessFile->close();
        }

        $file = new \File($this->getImageFilePath($videoId));
        $file->truncate();
        $file->write($content);
        $file->close();
    }

    /**
     * Get the image cache file path
     *
     * @param string $videoId
     *
     * @return string
     */
    protected function getImageFilePath($videoId)
    {
        return $this->imagesFolder . '/' . $videoId . '.jpg';
    }
}