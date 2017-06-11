<?php

namespace HTMLPageExcerpt\Asset\Finder;

abstract class AbstractAssetFinder
{
    /** @var array */
    protected $config;

    /** @var \DOMDocument */
    protected $dom;

    /** @var \DOMXPath */
    protected $xpath;

    /**
     * @param array        $config
     * @param \DOMDocument $dom
     * @param \DOMXPath    $xpath
     */
    public function __construct(array $config, \DOMDocument $dom, \DOMXPath $xpath)
    {
        $this->config = $config;
        $this->dom    = $dom;
        $this->xpath  = $xpath;
    }
}
