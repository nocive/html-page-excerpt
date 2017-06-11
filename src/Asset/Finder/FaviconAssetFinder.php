<?php

namespace HTMLPageExcerpt\Asset\Finder;

use HTMLPageExcerpt\Asset\Image;
use HTMLPageExcerpt\Asset\Url;
use HTMLPageExcerpt\Exception\CommunicationException;
use HTMLPageExcerpt\Exception\InvalidImageFileException;

class FaviconAssetFinder extends AbstractAssetFinder implements AssetFinderInterface
{
    const DEFAULT_FAVICON = '/favicon.ico';

    /** @var Url */
    protected $url;

    /**
     * @param array        $config
     * @param \DOMDocument $dom
     * @param \DOMXPath    $xpath
     * @param string       $url
     */
    public function __construct(array $config, \DOMDocument $dom, \DOMXPath $xpath, $url)
    {
        parent::__construct($config, $dom, $xpath);

        $this->url = $url;
    }

    /**
     * @return Image|null
     */
    public function find()
    {
        $favicon = null;

        // first try to find any <link rel="icon"> tags for an "announced" favicon
        $elements = $this->xpath->query('/html/head/link[contains(@rel, "icon")]/@href');
        foreach ($elements as $elem) {
            $candidate = new Image($elem->nodeValue, false);
            $candidate->url->absolutize((string)$this->url);

            if (!$this->url->isValid()) {
                continue;
            }

            try {
                $candidate->identify();
                if ($candidate->matches($this->config)) {
                    // found
                    $favicon = $candidate;
                    break;
                }
            } catch (InvalidImageFileException $e) {
            } catch (CommunicationException $e) {
            }
        }

        // if nothing was found, look for it in the default location http://domain/favicon.ico
        if (empty($favicon)) {
            $defaultFavicon = parse_url($this->url, PHP_URL_SCHEME) . '://' . parse_url($this->url, PHP_URL_HOST) . static::DEFAULT_FAVICON;

            try {
                $candidate = new Image($defaultFavicon, true);
                if ($candidate->matches($this->config)) {
                    // found
                    $favicon = $candidate;
                }
            } catch (InvalidImageFileException $e) {
            } catch (CommunicationException $e) {
            }
        }

        return $favicon;
    }
}
