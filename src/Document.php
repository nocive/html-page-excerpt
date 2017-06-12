<?php

namespace HTMLPageExcerpt;

use HTMLPageExcerpt\Asset\Finder\ExcerptAssetFinder;
use HTMLPageExcerpt\Asset\Finder\FaviconAssetFinder;
use HTMLPageExcerpt\Asset\Finder\ThumbnailsAssetFinder;
use HTMLPageExcerpt\Asset\Finder\TitleAssetFinder;
use HTMLPageExcerpt\Asset\Image;
use HTMLPageExcerpt\Asset\Text;
use HTMLPageExcerpt\Asset\Url;
use HTMLPageExcerpt\Exception\FatalException;

class Document
{
    const FIELD_TITLE       = 'title';
    const FIELD_THUMBS      = 'thumbs';
    const FIELD_EXCERPT     = 'excerpt';
    const FIELD_FAVICON     = 'favicon';

    const OPEN_GRAPH_NS     = 'og';
    const OPEN_GRAPH_NS_URL = 'http://ogp.me/ns#';

    /** @var \DOMDocument */
    protected $dom;

    /** @var \DOMXPath */
    protected $xpath;

    /** @var string */
    protected $html;

    /** @var Url */
    protected $url;

    /** @var Config */
    protected $config;

    /** @var Text */
    protected $title;

    /** @var Text */
    protected $excerpt;

    /** @var Image[] */
    protected $thumbnails;

    /** @var Image */
    protected $favicon;

    /** @var array */
    protected $fields = array(
        self::FIELD_TITLE,
        self::FIELD_EXCERPT,
        self::FIELD_THUMBS,
        self::FIELD_FAVICON,
    );

    /**
     * @param Config $config
     * @param Url    $url
     * @param string $html
     */
    public function __construct(Config $config, Url $url, $html)
    {
        $this->config = $config;
        $this->url = $url;

        $this->dom = new \DOMDocument();
        $this->dom->preserveWhitespace = false;

        $this->load($html);
    }

    /**
     * @param $html
     */
    protected function load($html)
    {
        @$this->dom->loadHTML($html);
        $this->dom->encoding = $this->detectEncoding();

        $html = $this->repairHTML($html, $this->dom->encoding, $this->config->get('main', 'encoding'));

        @$this->dom->loadHTML($html);

        // this must come after loadHTML
        $this->xpath = new \DOMXPath($this->dom);
        $this->xpath->registerNamespace(static::OPEN_GRAPH_NS, static::OPEN_GRAPH_NS_URL);

        $this->html = $html;
    }

    /**
     * @param  mixed $fields
     * @param  bool  $flatten
     *
     * @return mixed
     *
     * @throws FatalException
     */
    public function get($fields = '*', $flatten = false)
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        $getAll = false;
        if ($fields === array('*')) {
            $fields = $this->fields;
            $getAll = true;
        }

        $data = array();
        foreach ($fields as $f) {
            if ($getAll || in_array($f, $this->fields)) {
                $data[$f] = $this->find($f);
                if ($flatten) {
                    if (is_array($data[$f])) {
                        foreach ($data[$f] as &$v) {
                            $v = (string)$v;
                        }
                    } else {
                        $data[$f] = (string)$data[$f];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return (string) $this->url;
    }

    /**
     * @param  string $what
     *
     * @return mixed
     */
    protected function find($what)
    {
        $what = strtolower($what);
        if (in_array($what, $this->fields, true)) {
            // return if already cached
            if (isset($this->{$what}) && $this->{$what} !== null) {
                return $this->{$what};
            }

            return $this->{'find' . ucfirst($what)}();
        }

        return null;
    }

    /**
     * @return Text
     */
    protected function findTitle()
    {
        $finder = new TitleAssetFinder($this->config->get('finder:title'), $this->dom, $this->xpath);
        $this->title = $finder->find();

        return $this->title;
    }

    /**
     * @return Text
     */
    public function findExcerpt()
    {
        $finder = new ExcerptAssetFinder($this->config->get('finder:excerpt'), $this->dom, $this->xpath);
        $this->excerpt = $finder->find();

        return $this->excerpt;
    }

    /**
     * @return Image[]
     */
    public function findThumbs()
    {
        $finder = new ThumbnailsAssetFinder($this->config->get('finder:thumbs'), $this->dom, $this->xpath, $this->url);
        $this->thumbnails = $finder->find();

        return $this->thumbnails;
    }

    /**
     * @return Image
     */
    public function findFavicon()
    {
        $finder = new FaviconAssetFinder($this->config->get('finder:favicon'), $this->dom, $this->xpath, $this->url);
        $this->favicon = $finder->find();

        return $this->favicon;
    }

    protected function detectEncoding()
    {
        // get encoding from announced http content type header, if any
        if (!empty($this->url->encoding)) {
            return $this->url->encoding;
        }

        // fallback to meta http-equiv content-type tag, if no document encoding detected
        if (empty($this->dom->encoding)) {
            // if DOMDocument fails to find correct encoding, try to get it from meta tags
            $xpath = new \DOMXPath($this->dom);
            $elements = $xpath->query('/html/head/meta[@http-equiv="Content-Type"]/@content');
            unset($xpath);

            $matches = array();
            if ($elements->length === 1 && preg_match('@^.+;\s*charset=(.*)$@', $elements->item(0)->nodeValue, $matches)) {
                return $matches[1];
            }
        }

        // assume default encoding if no encoding found
        return empty($this->dom->encoding) ? $this->config->get('main', 'encoding') : $this->dom->encoding;
    }

    /**
     * @param  string $html
     * @param  string $fromEncoding
     * @param  string $toEncoding
     *
     * @return string
     */
    protected static function repairHTML($html, $fromEncoding, $toEncoding)
    {
        // hack for erroneous encodings (eg: http://tvnet.sapo.pt/noticias/detalhes.php?id=68085)
        // maybe it's possible to find a generic solution that replaces all invalid chars with their valid correspondents
        if (strtoupper($fromEncoding) === 'UTF-8') {
            $html = str_replace(array("\xe1", "\xe3", "\xe7", "\xea"), array('á', 'ã', 'ç', 'ê'), $html);
        }

        if (!empty($fromEncoding)) {
            if (strtoupper($fromEncoding) !== strtoupper($toEncoding)) {
                $html = static::convertEncoding($html, $fromEncoding, $toEncoding);
            }
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', $toEncoding);
        }

        // remove script, style and iframe tags
        $html = preg_replace('@<\s*\b(script|style|iframe)\b[^>]*>(.*?)<\s*\/\s*(script|style|iframe)\s*>@is', '', $html);

        // replace some utf8 weirdness (probably from MS office cpy&paste)
        $html = str_replace(array("\xc2\x92", "\xc2\x93", "\xc2\x94"), array('&rsquo;', '&ldquo;', '&rdquo;'), $html);

        // workaround to turn erroneous quotes into correct ones
        $html = str_replace(array('&#146;', '&#147;', '&#148;', '&#150;'), array('&#8217;', '&#8220;', '&#8221;', '&#8722;'), $html);

        return $html;
    }

    protected static function convertEncoding($str, $from, $to)
    {
        return @iconv($from, $to, $str);
    }
}
