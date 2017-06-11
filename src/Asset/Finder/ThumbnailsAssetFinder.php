<?php

namespace HTMLPageExcerpt\Asset\Finder;

use HTMLPageExcerpt\Asset\Image;
use HTMLPageExcerpt\Asset\Url;
use HTMLPageExcerpt\Exception\CommunicationException;
use HTMLPageExcerpt\Exception\InvalidImageFileException;

class ThumbnailsAssetFinder extends AbstractAssetFinder implements AssetFinderInterface
{
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
     * @return Image[]
     */
    public function find()
    {
        $thumbnails = array();
        $tries = 0;

        // first method: search <meta propert="og:image" content="">
        $elements = $this->xpath->query('/html/head/meta[@property="og:image"]/@content');
        foreach ($elements as $elem) {
            $candidate = new Image($elem->nodeValue, false);
            $candidate->url->absolutize((string)$this->url);

            // blacklisting check shouldn't be necessary for og:image
            // just check if it's a valid url
            if (!$candidate->url->isValid()) {
                continue;
            }

            try {
                $candidate->identify();
                if ($candidate->matches($this->config)) {
                    $thumbnails[] = $candidate;
                    break;
                }
            } catch (InvalidImageFileException $e) {
            } catch (CommunicationException $e) {
            }

            if (++$tries > $this->config['max_tries']) {
                break;
            }
        }

        // only try other methods if og:image was not found or was invalid
        if (empty($thumbnails)) {
            $thumbs = array();
            // second method: search for found img tags
            $elements = $this->xpath->query('/html/body//img/@src');
            foreach ($elements as $elem) {
                $candidate = new Image($elem->nodeValue, false);
                $candidate->url->absolutize((string)$this->url);

                if (!$candidate->url->isValid() || $candidate->url->matches($this->config['url_blacklist'])) {
                    continue;
                }
                $thumbs[] = $candidate;
            }

            // all Url class instances will be converted to string after calling array_unique
            $thumbs = array_unique($thumbs);

            // we should validate after array_unique, otherwise we could be wasting a lot of resources on repeated url's
            $foundStopCount = $this->config['found_stop_count'];
            foreach ($thumbs as $t) {
                /** @var $t Image */
                try {
                    $t->identify();
                    if ($t->matches($this->config)) {
                        $thumbnails[] = $t;
                        if (!empty($foundStopCount) && count($thumbnails) >= $foundStopCount) {
                            break;
                        }
                    }
                } catch (InvalidImageFileException $e) {
                } catch (CommunicationException $e) {
                }

                if (++$tries > $this->config['max_tries']) {
                    break;
                }
            }
        }

        return $thumbnails;
    }
}
