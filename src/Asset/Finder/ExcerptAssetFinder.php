<?php

namespace HTMLPageExcerpt\Asset\Finder;

use HTMLPageExcerpt\Asset\Text;

class ExcerptAssetFinder extends AbstractAssetFinder implements AssetFinderInterface
{
    /**
     * @return Text
     */
    public function find()
    {
        $excerpt = null;
        $ignFiltersForSEOTags = $this->config['seo_tags_ignore_filters'];

        foreach ($this->config['search_methods'] as $method) {
            switch ($method) {
                case 'meta/og:description':
                    // method: search <meta property="og:description" content="" />
                    // try to find a meta tag with property og:description as according to
                    // open graph protocol (@see http://developers.facebook.com/docs/opengraph/)
                    $elements = $this->xpath->query('/html/head/meta[@property="og:description"]/@content');
                    foreach ($elements as $elem) {
                        $candidate = new Text($elem->nodeValue, true);
                        if (!$candidate->isEmpty() && ($ignFiltersForSEOTags || $candidate->matches($this->config))) {
                            // found
                            $excerpt = $candidate;
                            break 3;
                        }
                    }
                    break;
                case 'meta/description':
                    // method: search <meta name="description" content="" />
                    $elements = $this->xpath->query('/html/head/meta[@name="description"]/@content');
                    foreach ($elements as $elem) {
                        $candidate = new Text($elem->nodeValue, true);
                        if (!$candidate->isEmpty() && ($ignFiltersForSEOTags || $candidate->matches($this->config))) {
                            // found
                            $excerpt = $candidate;
                            break 3;
                        }
                    }
                    break;
                case 'article/section':
                    // new html5 method
                    $elements = $this->xpath->query('/html/body//article/section');
                    foreach ($elements as $elem) {
                        $candidate = new Text($elem->nodeValue, true);
                        if ($candidate->matches($this->config)) {
                            // found
                            $excerpt = $candidate;
                            break 3;
                        }
                    }
                    break;
                default:
                    // default behaviour: search "normal" tags for text
                    $elements = $this->dom->getElementsByTagName($method);
                    foreach ($elements as $elem) {
                        $candidate = new Text(static::DOMInnerHTML($elem), true);
                        if ($candidate->matches($this->config)) {
                            // found
                            $excerpt = $candidate;
                            break 3;
                        }
                    }
            } // end switch
        } // end foreach

        if ($excerpt instanceof Text) {
            if ($this->config['truncate']) {
                $excerpt->truncate($this->config['truncate_length'], $this->config['truncate_terminator']);
            }
            if ($this->config['linkify']) {
                $excerpt->linkify();
            }
        }

        return $excerpt;
    }
}
