<?php

namespace HTMLPageExcerpt;

interface FileInfoInterface
{
    public static function mimeTypeToExtension($mimeType);
    public static function extensionToMimeType($extension);
    public static function detectMimeType($filename);
}
