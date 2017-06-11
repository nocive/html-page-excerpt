<?php

namespace HTMLPageExcerpt;

use HTMLPageExcerpt\Asset\Url;
use HTMLPageExcerpt\Exception\FatalException;
use HTMLPageExcerpt\Http\HttpFetcher;

final class Main
{
    /** @var Url */
    protected $url;

    /** @var Document */
    private $document;

    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        HttpFetcher::setDefaultOptions($config->get('fetcher'));
    }

    /**
     * @param  string $filename
     *
     * @return static
     */
    public static function createFromIni($filename)
    {
        $config = Config::fromIni($filename);

        return new static($config);
    }

    /**
     * @param  array $config
     *
     * @return static
     */
    public static function create(array $config)
    {
        return new static(new Config($config));
    }

    /**
     * @param  string $url
     *
     * @throws FatalException
     */
    public function loadURL($url)
    {
        $url = new Url($url);
        if (!$url->isValid()) {
            throw new FatalException(sprintf('Url "%s" is not a valid absolute url', $url));
        }
        $url->fetch();
        $html = $url->content();
        $this->loadDocument($url, $html);
    }

    /**
     * @param  string $html
     * @param  string $url
     *
     * @throws FatalException
     */
    public function loadHTML($url, $html)
    {
        $url = new Url($url);
        if (!$url->isValid()) {
            throw new FatalException(sprintf('Url "%s" is not a valid absolute url', $url));
        }

        $this->loadDocument($url, $html);
    }

    /**
     * @param  array|string $fields
     * @param  bool         $flatten
     *
     * @return mixed
     */
    public function get($fields, $flatten = true)
    {
        return $this->document->get($fields, $flatten);
    }

    /**
     * @param string $url
     * @param string $html
     */
    protected function loadDocument($url, $html)
    {
        $this->document = new Document($this->config, $url, $html);
    }
}
