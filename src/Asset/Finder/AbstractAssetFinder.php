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

    /**
     * @param  \DOMNode $element
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected static function DOMinnerHTML(\DOMNode $element)
    {
        $innerHTML = '';
        $children = $element->childNodes;
        foreach ($children as $child) {
            $tmpDom = new \DOMDocument();
            $tmpDom->appendChild($tmpDom->importNode($child, true));
            $innerHTML .= trim($tmpDom->saveHTML());
        }

        return $innerHTML;
    }
}
