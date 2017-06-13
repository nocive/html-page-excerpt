<?php

namespace HTMLPageExcerpt;

interface FileInfoInterface
{
    public function mimeTypeToExtension($mimeType);
    public function extensionToMimeType($extension);
    public function detectMimeType($filename);
}
