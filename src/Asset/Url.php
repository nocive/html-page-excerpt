<?php

namespace HTMLPageExcerpt\Asset;

use HTMLPageExcerpt\Exception\FileIOException;
use HTMLPageExcerpt\Exception\CommunicationException;
use HTMLPageExcerpt\Http\HttpFetcher;

class Url implements AssetInterface
{
    /** @var string */
    public $url;

    /** @var string */
    public $content;

    /** @var string */
    public $contentType;

    /** @var string */
    public $encoding;

    /** @var HttpFetcher */
    protected $httpFetcher;

    /** @var bool */
    protected $fetched = false;

    /**
     * @param string     $url
     * @param bool       $sanitize
     * @param bool       $fetch
     */
    public function __construct($url = null, $sanitize = true, $fetch = false)
    {
        if ($url !== null) {
            $this->set($url, $sanitize);
            if ($fetch) {
                $this->fetch();
            }
        }

        $this->httpFetcher = new HttpFetcher();
    }

    /**
     * @param  string $url
     * @param  bool   $sanitize
     *
     * @return string
     */
    public function set($url, $sanitize = true)
    {
        if ($sanitize) {
            $url = $this->sanitize($url);
        }
        $this->url = $url;
        $this->fetched = false;

        return $url;
    }

    /**
     * @param  string $base
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function absolutize($base)
    {
        if (!is_string($base) || !$this->isAbsolute($base)) {
            throw new \InvalidArgumentException("Supplied base url '$base' is not a valid absolute url");
        }

        $url = $this->url;

        if (empty($base) || empty($url) || $this->isAbsolute($url)) {
            return $url;
        }

        // queries and anchors
        if ($url[0] == '#' || $url[0] == '?') {
            return $this->url = $base . $url;
        }

        static $defaultParts = array(
            'scheme'   => '',
            'host'     => '',
            'port'     => '',
            'path'     => '',
            'user'     => '',
            'pass'     => '',
            'query'    => '',
            'fragment' => '',
        );
        $parts = array_merge($defaultParts, parse_url($base));

        // remove non-directory element from path
        $parts['path'] = preg_replace('@/[^/]*$@', '', $parts['path']);

        // destroy path if relative url points to root
        if ($url[0] == '/') {
            $parts['path'] = '';
        }

        // dirty absolute URL
        $abs = $parts['host'] . (($parts['port'] !== '' && $parts['port'] !== 80) ? ":{$parts['port']}" : '') . "{$parts['path']}/{$url}";

        // replace '//' or '/./' or '/foo/../' with '/'
        $re = array(
            '@(/\.?/)@',
            '@/(?!\.\.)[^/]+/\.\./@'
        );
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        $url = $parts['scheme'] . '://' . $abs;

        return $this->url = $url;
    }

    /**
     * @param  string $str
     *
     * @return bool
     */
    public function isAbsolute($str = null)
    {
        $str = ($str === null) ? $this->url : $str;

        return (bool)preg_match('@^https?://.+$@i', $str);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isAbsolute();
    }

    /**
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws CommunicationException
     */
    public function fetch()
    {
        if (!$this->isAbsolute()) {
            throw new \InvalidArgumentException("Url '{$this->url}' is not a valid absolute url");
        }

        $this->httpFetcher->fetch($this->url);

        $this->fetched     = true;
        $this->content     = $this->httpFetcher->getContent();
        $this->contentType = $this->httpFetcher->getContentType();
        $this->encoding    = $this->httpFetcher->getEncoding();

        return $this->content;
    }

    /**
     * @param  string $path
     * @param  int    $perms
     *
     * @throws FileIOException
     */
    public function save($path, $perms = 0666)
    {
        if (!$this->fetched) {
            $this->fetch();
        }

        if (false === @file_put_contents($path, $this->content(), $perms)) {
            throw new FileIOException("Error saving contents to file '$path'");
        }
    }

    /**
     * @param  string $pattern
     *
     * @return bool
     */
    public function matches($pattern)
    {
        return (bool) preg_match('@' . preg_quote($pattern, '@') .'@i', $this->url);
    }

    /**
     * @return string
     */
    public function content()
    {
        if (!$this->fetched) {
            $this->fetch();
        }

        return $this->content;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->url;
    }

    /**
     * @param  string $url
     *
     * @return string
     */
    public function sanitize($url)
    {
        return trim($url);
    }
}
