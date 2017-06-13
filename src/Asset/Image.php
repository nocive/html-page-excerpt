<?php

namespace HTMLPageExcerpt\Asset;

use HTMLPageExcerpt\Exception\FatalException;
use HTMLPageExcerpt\Exception\FileIOException;
use HTMLPageExcerpt\Exception\InvalidImageFileException;
use HTMLPageExcerpt\FileInfo;
use HTMLPageExcerpt\FileInfoInterface;

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

    /** @var FileInfoInterface */
    protected $fileInfo;

    /**
     * @param string $url
     * @param bool   $identify
     */
    public function __construct($url, $identify = false)
    {
        $this->url = new Url($url, $sanitize = true, $fetch = false);
        $this->fileInfo = new FileInfo();

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

        $this->tmpFilename = static::tempFilename();
        $this->url->fetch();
        $this->url->save($this->tmpFilename);
        // free some memory by discarding the content
        unset($this->url->content);

        $this->mimeType = $this->fileInfo->detectMimeType($this->tmpFilename);
        if (strpos($this->mimeType, 'image/') !== 0) {
            throw new InvalidImageFileException(
                sprintf('Image %s (%s) is not a valid image', (string) $this->url, $this->tmpFilename)
            );
        }

        $this->size = filesize($this->tmpFilename);

        // skip svgs as they have no real size
        if (strpos($this->mimeType, 'image/svg') !== 0) {
            if (false === ($imgInfo = @getimagesize($this->tmpFilename))) {
                throw new FatalException(
                    sprintf('Error calling getimagesize() on image %s (%s)', (string) $this->url, $this->tmpFilename)
                );
            }
            $this->width = $imgInfo[0];
            $this->height = $imgInfo[1];
        }
        $this->extension = $this->fileInfo->mimeTypeToExtension($this->mimeType);
        $this->identified = true;
    }

    /**
     * @param  array $criteria
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

        $c = array_merge(array(
            'mimetypes_include'  => array(),
            'mimetypes_exclude'  => array(),
            'extensions_include' => array(),
            'extensions_exclude' => array(),
            'min_width'          => null,
            'max_width'          => null,
            'min_height'         => null,
            'max_height'         => null,
            'min_size'           => null,
            'max_size'           => null,
        ), $criteria);

        if (
            (!empty($c['mimetypes_exclude']) || !empty($c['mimetypes_include'])) &&
            (!empty($c['extensions_exclude']) || !empty($c['extensions_include']))
        ) {
            throw new FatalException(
                'Cannot do mimetypes include/exclude together with extensions include/exclude, use only one of both'
            );
        }

        if (!empty($c['extensions_include'])) {
            if (empty($this->extension)) {
                throw new FatalException('Extension is empty, cannot do matching');
            }

            $c['mimetypes_include'] = array();
            foreach ($c['extensions_include'] as $ext) {
                $mime = $this->fileInfo->extensionToMimeType($ext);
                if (!empty($mime)) {
                    $c['mimetypes_include'][] = $mime;
                }
            }
        }

        if (!empty($c['extensions_exclude'])) {
            if (empty($this->extension)) {
                throw new FatalException('Extension is empty, cannot do matching');
            }

            $c['mimetypes_exclude'] = array();
            foreach ($c['extensions_exclude'] as $ext) {
                $mime = $this->fileInfo->extensionToMimeType($ext);
                if (!empty($mime)) {
                    $c['mimetypes_exclude'][] = $mime;
                }
            }
        }

        if (!empty($c['mimetypes_include'])) {
            if (empty($this->mimeType)) {
                throw new FatalException('Mimetype is empty, cannot do matching');
            }

            if (!in_array($this->mimeType, $c['mimetypes_include'], true)) {
                return false;
            }
        }

        if (!empty($c['mimetypes_exclude'])) {
            if (empty($this->mimeType)) {
                throw new FatalException('Mimetype is empty, cannot do matching');
            }

            if (in_array($this->mimeType, $c['mimetypes_exclude'], true)) {
                return false;
            }
        }

        if (empty($this->width) && (!empty($c['min_width']) || !empty($c['max_width']))) {
            throw new FatalException('Width is empty, cannot do matching');
        }

        if (empty($this->height) && (!empty($c['min_height']) || !empty($c['max_height']))) {
            throw new FatalException('Height is empty, cannot do matching');
        }

        return (
            (empty($c['min_width']) || $this->width >= $c['min_width']) &&
            (empty($c['max_width']) || $this->width <= $c['max_width']) &&
            (empty($c['min_height']) || $this->height >= $c['min_height']) &&
            (empty($c['max_height']) || $this->height <= $c['max_height']) &&
            (empty($c['min_size']) || $this->size >= $c['min_size']) &&
            (empty($c['max_size']) || $this->size <= $c['max_size'])
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
            throw new FileIOException(sprintf('Could not read contents of file "%s"', $this->tmpFilename));
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
