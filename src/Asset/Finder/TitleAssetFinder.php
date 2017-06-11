<?php

namespace HTMLPageExcerpt\Asset\Finder;

use HTMLPageExcerpt\Asset\Text;

class TitleAssetFinder extends AbstractAssetFinder implements AssetFinderInterface
{
    /**
     * @return Text
     */
    public function find()
    {
        $title = null;
        $ignFiltersForSEOTags = $this->config['seo_tags_ignore_filters'];

        foreach ($this->config['search_methods'] as $method) {
            switch ($method) {
                case 'meta/og:title':
                    $elements = $this->xpath->query('/html/head/meta[@property="og:title"]/@content');
                    foreach ($elements as $elem) {
                        $candidate = new Text($elem->nodeValue, true);
                        if (!$candidate->isEmpty() && ($ignFiltersForSEOTags || $candidate->matches($this->config))) {
                            // found
                            $title = $candidate;
                            break 3;
                        }
                    }
                    break;
                case 'title':
                    $elements = $this->xpath->query('/html/head/title');
                    foreach ($elements as $elem) {
                        $candidate = new Text($elem->nodeValue, true);
                        if (!$candidate->isEmpty() && ($ignFiltersForSEOTags || $candidate->matches($this->config))) {
                            // found
                            $title = $candidate;
                            break 3;
                        }
                    }
                    break;
                default:
                    $elements = $this->dom->getElementsByTagName($method);
                    foreach ($elements as $elem) {
                        $candidate = new Text($elem->nodeValue, true);
                        if (!$candidate->isEmpty() && $candidate->matches($this->config)) {
                            // found
                            $title = $candidate;
                            break 3;
                        }
                    }
            } // end switch
        } // end foreach

        if ($this->config['truncate'] && $title instanceof Text) {
            $title->truncate($this->config['truncate_length'], $this->config['truncate_terminator']);
        }

        return $title;
    }
}
