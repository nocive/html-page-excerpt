<?php

namespace HTMLPageExcerpt;

use Mimey\MimeTypes;
use Mimey\MimeTypesInterface;

class FileInfo implements FileInfoInterface
{
    /** @var MimeTypesInterface */
    private static $mimey;

    /** @var \finfo */
    private static $finfo;

    public function __construct()
    {
        if (null === static::$mimey) {
            static::$mimey = new MimeTypes();
        }

        if (null === static::$finfo) {
            static::$finfo = new \finfo(FILEINFO_MIME_TYPE);
        }
    }

    /**
     * @param  string $mimeType
     *
     * @return string
     */
    public function mimeTypeToExtension($mimeType)
    {
        return static::$mimey->getExtension($mimeType);
    }

    /**
     * @param  string $extension
     *
     * @return string
     */
    public function extensionToMimeType($extension)
    {
        return static::$mimey->getMimeType($extension);
    }

    /**
     * @param  string $filename
     *
     * @return string
     */
    public function detectMimeType($filename)
    {
        return static::$finfo->file($filename);
    }
}
