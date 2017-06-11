<?php

namespace HTMLPageExcerpt\Asset;

use HTMLPageExcerpt\Exception\FatalException;
use HTMLPageExcerpt\Exception\FileIOException;
use HTMLPageExcerpt\Exception\InvalidImageFileException;
use HTMLPageExcerpt\FileInfo;

class Image implements AssetInterface
{
    const TEMPFILE_PREFIX = 'peimg_';
    const FIELD_URL       = 'url';
    const FIELD_WIDTH     = 'width';
    const FIELD_HEIGHT    = 'height';
    const FIELD_MIMETYPE  = 'mimetype';
    const FIELD_EXTENSION = 'extension';
    const FIELD_SIZE      = 'size';

    /** @var Url */
    public $url;

    /** @var int */
    public $width = 0;

    /** @var int */
    public $height = 0;

    /** @var string */
    public $mimeType;

    /** @var string */
    public $extension;

    /** @var int */
    public $size = 0;

    /** @var array */
    protected $fields = array(
        self::FIELD_URL,
        self::FIELD_WIDTH,
        self::FIELD_HEIGHT,
        self::FIELD_MIMETYPE,
        self::FIELD_EXTENSION,
        self::FIELD_SIZE
    );

    /** @var bool */
    protected $identified = false;

    /** @var string */
    protected $tmpFilename;

    /**
     * @param string $url
     * @param bool   $identify
     */
    public function __construct($url, $identify = false)
    {
        $this->url = new Url($url, $sanitize = true, $fetch = false);
        if ($identify) {
            $this->identify();
        }
    }

    public function __destruct()
    {
        if ($this->tmpFilename) {
            @unlink($this->tmpFilename);
        }
    }

    /**
     * @throws InvalidImageFileException
     * @throws FatalException
     */
    public function identify()
    {
        if ($this->identified) {
            return;
        }

        $this->url->fetch();
        $this->tmpFilename = static::tempFilename(static::TEMPFILE_PREFIX);
        $this->url->save($this->tmpFilename);
        // free some memory
        unset($this->url->content);

        $this->mimeType = FileInfo::detectMimeType($this->tmpFilename);
        if (strpos($this->mimeType, 'image/') !== 0) {
            throw new InvalidImageFileException("File '{$this->tmpFilename}' is not a valid image");
        }

        $this->size = filesize($this->tmpFilename);

        // skip svgs as they have no real size
        if (strpos($this->mimeType, 'image/svg') !== 0) {
            if (false === ($imgInfo = @getimagesize($this->tmpFilename))) {
                throw new FatalException("Error calling getimagesize() on image '{$this->tmpFilename}'");
            }
            $this->width = $imgInfo[0];
            $this->height = $imgInfo[1];
        }
        $this->extension = FileInfo::mimeTypeToExtension($this->mimeType);

        $this->identified = true;
    }

    /**
     * @param  array $criteria   Possible criterias: mimetypes_include, mimetypes_exclude, extensions_include,
     *                           extensions_exclude, min_width, max_width, min_height, max_height, min_size, max_size
     *
     * @return bool
     *
     * @throws FatalException
     */
    public function matches(array $criteria)
    {
        if (!$this->identified) {
            $this->identify();
        }

        // TODO drop usage of extract
        extract($criteria);

        if ((!empty($mimetypes_exclude) || !empty($mimetypes_include)) && (!empty($extensions_exclude) || !empty($extensions_include))) {
            throw new FatalException('Can\'t do mimetypes include/exclude together with extensions include/exclude, use only one of both');
        }

        if (!empty($extensions_include)) {
            if (empty($this->extension)) {
                throw new FatalException('Extension is empty can\'t do matching');
            }

            $mimetypes_include = array();
            foreach ($extensions_include as $ext) {
                $mime = FileInfo::extensionToMimeType($ext);
                if (!empty($mime)) {
                    $mimetypes_include[] = $mime;
                }
            }
        }

        if (!empty($extensions_exclude)) {
            if (empty($this->extension)) {
                throw new FatalException('Extension is empty can\'t do matching');
            }

            $mimetypes_exclude = array();
            foreach ($extensions_exclude as $ext) {
                $mime = FileInfo::extensionToMimeType($ext);
                if (!empty($mime)) {
                    $mimetypes_exclude[] = $mime;
                }
            }
        }

        if (!empty($mimetypes_include)) {
            if (empty($this->mimeType)) {
                throw new FatalException('Mimetype is empty can\'t do matching');
            }
            if (!is_array($mimetypes_include)) {
                $mimetypes_include = (array)$mimetypes_include;
            }

            if (!in_array($this->mimeType, $mimetypes_include)) {
                return false;
            }
        }

        if (!empty($mimetypes_exclude)) {
            if (empty($this->mimeType)) {
                throw new FatalException('Mimetype is empty can\'t do matching');
            }
            if (!is_array($mimetypes_exclude)) {
                $mimetypes_exclude = (array)$mimetypes_exclude;
            }

            if (in_array($this->mimeType, $mimetypes_exclude)) {
                return false;
            }
        }

        if (empty($this->width) && (!empty($min_width) || !empty($max_width))) {
            throw new FatalException('Width is empty can\'t do matching');
        }

        if (empty($this->height) && (!empty($min_height) || !empty($max_height))) {
            throw new FatalException('Height is empty can\'t do matching');
        }

        return (
            (empty($min_width) || $this->width >= $min_width) &&
            (empty($max_width) || $this->width <= $max_width) &&
            (empty($min_height) || $this->height >= $min_height) &&
            (empty($max_height) || $this->height <= $max_height) &&
            (empty($min_size) || $this->size >= $min_size) &&
            (empty($max_size) || $this->size <= $max_size)
        );
    }

    /**
     * @param  mixed $fields
     *
     * @return mixed
     */
    public function info($fields = null)
    {
        if (!$this->identified) {
            $this->identify();
        }

        if ($fields !== null && !is_array($fields)) {
            $fields = (array)$fields;
        }

        $getAll = ($fields === null || $fields === array('*'));

        $data = array();
        foreach ($this->fields as $f) {
            if ($getAll || in_array($f, $fields)) {
                $value = $this->{$f};
                $data[$f] = $value instanceof Url ? (string) $value : $value;
            }
        }

        return $data;
    }

    /**
     * @param string $path
     * @param int    $perms
     */
    public function save($path, $perms = 0666)
    {
        $this->url->save($path, $perms);
    }

    /**
     * @return string
     *
     * @throws FileIOException
     */
    public function content()
    {
        if (!$this->identified) {
            $this->identify();
        }

        if (false === ($content = @file_get_contents($this->tmpFilename))) {
            throw new FileIOException("Could not read contents of file '{$this->tmpFilename}'");
        }

        return $content;
    }

    /**
     * This is required because array_unique typecasts to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->url;
    }

    /**
     * @return string
     */
    protected static function tempFilename()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . str_replace('.', '_', uniqid(static::TEMPFILE_PREFIX, true)) . '.tmp';
    }
}
