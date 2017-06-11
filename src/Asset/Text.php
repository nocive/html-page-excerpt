<?php

namespace HTMLPageExcerpt\Asset;

class Text implements AssetInterface
{
    const LINKIFY_TARGET = '_blank';

    /** @var string */
    protected $content;

    /** @var string */
    protected $encoding;

    /**
     * @param string $str
     * @param bool   $sanitize
     * @param string $encoding
     */
    public function __construct($str, $sanitize = true, $encoding = 'UTF-8')
    {
        $this->content = $sanitize ? $this->sanitize($str) : $str;
        $this->encoding = $encoding;
    }

    /**
     * @param  int    $length
     * @param  string $terminator
     *
     * @return string
     */
    public function truncate($length, $terminator = ' ...')
    {
        if (empty($this->content) || strlen($this->content) <= $this->content) {
            return $this->content;
        }

        $this->content = static::substrWords($this->content, $length, $terminator);

        return $this->content;
    }

    /**
     * @param  array $criteria   Possible criterias: min_length, max_length
     *
     * @return bool
     */
    public function matches(array $criteria)
    {
        return (
            (empty($criteria['min_length']) || strlen($this->content) >= $criteria['min_length']) &&
            (empty($criteria['max_length']) || strlen($this->content) <= $criteria['max_length'])
        );
    }

    /**
     * @param  string $str
     *
     * @return string
     */
    public function sanitize($str)
    {
        // add a space if a tag is present right next to a word
        $str = preg_replace('@([^\s]+)(<[^>]+>)@', '\\1 \\2', $str);

        $str = strip_tags(html_entity_decode($str, ENT_QUOTES, $this->encoding));

        // turn utf8 nbsp's into normal spaces
        // @see http://en.wikipedia.org/wiki/Non-breaking_space
        //$str = str_replace( "\xc2\xa0", ' ', $str );
        // remove extra whitespace
        //$str = trim( preg_replace( '@\s\s+@', ' ', $str ) );

        // replace all known unicode whitespaces with space
        $str = preg_replace('@[\pZ\pC]+@mu', ' ', $str);
        // remove spaces before commas
        $str = preg_replace('@\s,@', ',', $str);

        return trim($str);
    }

    /**
     * @param  string $target
     *
     * @return string
     */
    public function linkify($target = self::LINKIFY_TARGET)
    {
        $target = !empty($target) ? " target=\"$target\"" : '';
        $this->content = preg_replace('@(https?://[^\s]+)@', "<a href=\"\\1\"$target>\\1</a>", $this->content);

        return $this->content;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->content);
    }

    protected static function substrWords($str, $length, $terminator = '...', $minWord = 3)
    {
        $sub = '';
        $len = 0;

        foreach (explode(' ', $str) as $word) {
            $part = (($sub != '') ? ' ' : '') . $word;
            $sub .= $part;
            $len += strlen($part);

            if (strlen($word) > $minWord && strlen($sub) >= $length) {
                break;
            }
        }

        return $sub . (($len < strlen($str)) ? $terminator : '');
    }
}
