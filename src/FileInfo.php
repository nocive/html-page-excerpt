<?php

namespace HTMLPageExcerpt;

use Mimey\MimeTypesInterface;

class FileInfo implements FileInfoInterface
{
    /** @var MimeTypesInterface */
    private static $mimey;

    /** @var \finfo */
    private static $finfo;

    /**
     * @param  string $mimeType
     *
     * @return string
     */
    public static function mimeTypeToExtension($mimeType)
    {
        return self::getMimey()->getExtension($mimeType);
    }

    /**
     * @param  string $extension
     *
     * @return string
     */
    public static function extensionToMimeType($extension)
    {
        return self::getMimey()->getMimeType($extension);
    }

    /**
     * @param  string $filename
     *
     * @return string
     */
    public static function detectMimeType($filename)
    {
        return self::getFinfo()->file($filename);
    }

    private static function getMimey()
    {
        if (null === self::$mimey) {
            self::$mimey = new \Mimey\MimeTypes();
        }

        return self::$mimey;
    }

    private static function getFinfo()
    {
        if (null === self::$finfo) {
            self::$finfo = new \finfo(FILEINFO_MIME_TYPE);
        }

        return self::$finfo;
    }
}
