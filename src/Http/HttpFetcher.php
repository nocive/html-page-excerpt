<?php

namespace HTMLPageExcerpt\Http;

use HTMLPageExcerpt\Exception\CommunicationException;

class HttpFetcher
{
    /** @var string */
    protected $content;

    /** @var string */
    protected $contentType;

    /** @var string */
    protected $encoding;

    /** @var array */
    protected $options;

    /** @var array */
    protected static $defaultOptions = array(
        'connect_timeout' => false,
        'timeout'         => false,
        'max_redirs'      => 2,
        'user_agent'      => false,
        'follow_location' => true,
        'proxy'           => false,
        'fake_referer'    => true,
    );

    /**
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        $this->options = null === $options ? static::$defaultOptions : array_merge(static::$defaultOptions, $options);
    }

    /**
     * @param array $opts
     */
    public static function setDefaultOptions(array $opts)
    {
        static::$defaultOptions = array_merge(static::$defaultOptions, $opts);
    }

    /**
     * @param  string $url
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws CommunicationException
     */
    public function fetch($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if (isset($this->options['connect_timeout'])) {
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->options['connect_timeout']);
        }
        if (isset($this->options['timeout'])) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->options['timeout']);
        }
        if (!empty($this->options['user_agent'])) {
            curl_setopt($curl, CURLOPT_USERAGENT, $this->options['user_agent']);
        }
        if (isset($this->options['follow_location'])) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->options['follow_location']);
        }
        if (isset($this->options['max_redirs'])) {
            curl_setopt($curl, CURLOPT_MAXREDIRS, $this->options['max_redirs']);
        }
        if (!empty($this->options['proxy'])) {
            curl_setopt($curl, CURLOPT_PROXY, $this->options['proxy']);
        }
        if (isset($this->options['fake_referer'])) {
            curl_setopt($curl, CURLOPT_REFERER, $url);
        }
        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        $content = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        $encoding = null;
        $matches = array();
        if (preg_match('@^.+;\s*charset=(.*)$@', $contentType, $matches)) {
            $encoding = $matches[1];
        }

        if ($content === false || $httpStatusCode !== 200) {
            throw new CommunicationException(
                sprintf(
                    "Error fetching content from url '%s'%s",
                    $url,
                    curl_errno($curl) ? ', curl error: ' . curl_error($curl) : ''
                )
            );
        }

        curl_close($curl);

        $this->content     = $content;
        $this->contentType = $contentType;
        $this->encoding    = $encoding;

        return $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
