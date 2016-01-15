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
        return $this->readCacheFile($this->getDataFilePath($videoId));
    }

    /**
     * Set the video data
     *
     * @param string $videoId
     * @param array  $data
     */
    public function setVideoData($videoId, array $data)
    {
        $this->writeCacheFile($this->getDataFilePath($videoId), $data);
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
        return $this->dataFolder . '/video_' . $videoId . '.json';
    }

    /**
     * Return true if there is album data
     *
     * @param string $albumId
     *
     * @return bool
     */
    public function hasAlbumData($albumId)
    {
        $data = $this->getAlbumData($albumId);

        if ($data === null) {
            return false;
        }

        return true;
    }

    /**
     * Get the album data
     *
     * @param string $albumId
     *
     * @return array|null
     */
    public function getAlbumData($albumId)
    {
        return $this->readCacheFile($this->getAlbumFilePath($albumId));
    }

    /**
     * Set the album data
     *
     * @param string $albumId
     * @param array  $data
     */
    public function setAlbumData($albumId, array $data)
    {
        $this->writeCacheFile($this->getAlbumFilePath($albumId), $data);
    }

    /**
     * Get the album cache file path
     *
     * @param string $albumId
     *
     * @return string
     */
    protected function getAlbumFilePath($albumId)
    {
        return $this->dataFolder . '/album_' . $albumId . '.json';
    }

    /**
     * Return true if there is album videos data
     *
     * @param string $albumId
     *
     * @return bool
     */
    public function hasAlbumVideosData($albumId)
    {
        $data = $this->getAlbumVideosData($albumId);

        if ($data === null) {
            return false;
        }

        return true;
    }

    /**
     * Get the album videos data
     *
     * @param string $albumId
     *
     * @return array|null
     */
    public function getAlbumVideosData($albumId)
    {
        return $this->readCacheFile($this->getAlbumVideosFilePath($albumId));
    }

    /**
     * Set the album videos data
     *
     * @param string $albumId
     * @param array  $data
     */
    public function setAlbumVideosData($albumId, array $data)
    {
        $this->writeCacheFile($this->getAlbumVideosFilePath($albumId), $data);
    }

    /**
     * Get the album videos cache file path
     *
     * @param string $albumId
     *
     * @return string
     */
    protected function getAlbumVideosFilePath($albumId)
    {
        return $this->dataFolder . '/album_videos_' . $albumId . '.json';
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

    /**
     * Write the cache file
     *
     * @param string $path
     * @param array  $data
     */
    protected function writeCacheFile($path, array $data)
    {
        $file = new \File($path);
        $file->truncate();
        $file->write(json_encode($data));
        $file->close();
    }

    /**
     * Read the cache file
     *
     * @param string $path
     *
     * @return array|null
     */
    protected function readCacheFile($path)
    {
        if (!is_file(TL_ROOT . '/' . $path)) {
            return null;
        }

        $file = new \File($path, true);

        return json_decode($file->getContent(), true);
    }
}