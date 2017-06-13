<?php

namespace HTMLPageExcerpt;

use HTMLPageExcerpt\Asset\Url;
use HTMLPageExcerpt\Exception\FatalException;
use HTMLPageExcerpt\Http\HttpFetcher;

final class Main
{
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
            throw new FatalException(sprintf('Url "%s" is not a valid absolute url', (string) $url));
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
            throw new FatalException(sprintf('Url "%s" is not a valid absolute url', (string) $url));
        }

        $this->loadDocument($url, $html);
    }

    /**
     * @param  array|string $fields
     * @param  bool         $flatten
     *
     * @return mixed
     */
    public function get($fields, $flatten = false)
    {
        if (null === $this->document) {
            throw new \LogicException('Document not loaded, you need to load it via loadHTML() or loadURL() methods');
        }

        return $this->document->get($fields, $flatten);
    }

    /**
     * @return array
     */
    public function getAsArray()
    {
        if (null === $this->document) {
            throw new \LogicException('Document not loaded, you need to load it via loadHTML() or loadURL() methods');
        }

        return $this->document->get('*', $flatten = true);
    }

    /**
     * @return string
     */
    public function getAsJson()
    {
        if (null === $this->document) {
            throw new \LogicException('Document not loaded, you need to load it via loadHTML() or loadURL() methods');
        }

        return json_encode($this->getAsArray());
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param Url    $url
     * @param string $html
     */
    protected function loadDocument(Url $url, $html)
    {
        $this->document = new Document($this->config, $url, $html);
    }
}
