<?php

/**
 * Url class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	Url
 */
namespace HTMLPageExcerpt;

class Url extends Object
{
	/**
	 * @constant	string
	 */
	const URL_PARTS = 'scheme,host,port,user,pass,path,query,fragment';

	/**
	 * @var		string
	 * @access	public
	 */
	public $url;

	/**
	 * @var		string
	 * @access	public
	 */
	public $content;

	/**
	 * @var		string
	 * @access	public
	 */
	public $contentType;

	/**
	 * @var		string
	 * @access	public
	 */
	public $encoding;

	/**
	 * @var		resource
	 * @access	public
	 */
	protected $_curl;

	/**
	 * @var		bool
	 * @access	protected
	 */
	protected $_fetched = false;

	/**
	 * @var		array
	 * @access	protected
	 */
	protected $_validSchemes = array( 
			'http', 
			'https' 
	);


	/**
	 * Enter description here ...
	 * 
	 * @param	string $url	optional
	 * @param	bool $sanitize	optional
	 * @param	bool $fetch	optional
	 */
	public function __construct( $url = null, $sanitize = true, $fetch = false, $config = null )
	{
		parent::__construct( $config );

		if ($url !== null) {
			$url = $this->set( $url, $sanitize );
			if ($fetch) {
				$this->content = $this->fetch();
			}
		}
	} // __construct }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $url
	 * @param	bool $sanitize	optional
	 * @return	string
	 */
	public function set( $url, $sanitize = true )
	{
		if ($sanitize) {
			$url = $this->sanitize( $url );
		}
		$this->url = $url;
		$this->_fetched = false;
		return $url;
	} // set }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $base
	 * @throws	\InvalidArgumentException
	 * @return	string
	 */
	public function absolutize( $base )
	{
		if (! $this->isAbsolute( $base )) {
			throw new \InvalidArgumentException( "Supplied base url '$base' is not a valid absolute url" );
		}

		$url = $this->url;
		if (empty( $base ) || empty( $url ) || $this->isAbsolute( $url )) {
			return $url;
		}

		// queries and anchors
		if ($url[0] == '#' || $url[0] == '?') {
			$url = $base . $url;
			$this->url = $url;
			return $url;
		}

		extract( array_merge( explode( ',', self::URL_PARTS ), parse_url( $base ) ) );

		// remove non-directory element from path
		$path = preg_replace( '@/[^/]*$@', '', $path );

		/* destroy path if relative url points to root */
		if ($url[0] == '/') {
			$path = '';
		}

		// dirty absolute URL
		$abs = "$host" . (($port !== '' && $port !== 80) ? ":$port" : '') . "$path/{$url}";

		// replace '//' or '/./' or '/foo/../' with '/'
		$re = array( 
			'@(/\.?/)@', 
			'@/(?!\.\.)[^/]+/\.\./@' 
		);
		for ($n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, - 1, $n )) {
		}

		$url = $scheme . '://' . $abs;
		$this->url = $url;
		return $url;
	} // absolutize }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $str	optional
	 * @return	bool
	 */
	public function isAbsolute( $str = null )
	{
		$str = ($str === null) ? $this->url : $str;
		return (bool) preg_match( '@^(' . implode( '|', $this->_validSchemes ) . ')://.+$@i', $str );
	} // isAbsolute }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	bool
	 */
	public function isValid()
	{
		return $this->isAbsolute();
	} // isValid }}}


	/**
	 * Enter description here ...
	 * 
	 * @throws	\InvalidArgumentException
	 * @throws	CommunicationException
	 * @return	string
	 */
	public function fetch()
	{
		if (! $this->isAbsolute()) {
			throw new \InvalidArgumentException( "Url '{$this->url}' is not a valid absolute url" );
		}

		$cfgClass = $this->_config;
		$opts = array(
			$cfgClass::FETCHER_TIMEOUT => $this->_config->get( $cfgClass::FETCHER_TIMEOUT ),
			$cfgClass::FETCHER_USER_AGENT => $this->_config->get( $cfgClass::FETCHER_USER_AGENT ),
			$cfgClass::FETCHER_FOLLOW_LOCATION => $this->_config->get( $cfgClass::FETCHER_FOLLOW_LOCATION ),
			$cfgClass::FETCHER_MAX_REDIRS => $this->_config->get( $cfgClass::FETCHER_MAX_REDIRS ),
			$cfgClass::FETCHER_PROXY => $this->_config->get( $cfgClass::FETCHER_PROXY ),
			$cfgClass::FETCHER_FAKE_REFERER => $this->_config->get( $cfgClass::FETCHER_FAKE_REFERER ),
		);

		$this->_curl = curl_init();
		curl_setopt( $this->_curl, CURLOPT_URL, $this->url );
		curl_setopt( $this->_curl, CURLOPT_RETURNTRANSFER, true );
		if (isset( $opts[$cfgClass::FETCHER_TIMEOUT] )) {
			curl_setopt( $this->_curl, CURLOPT_CONNECTTIMEOUT, $opts[$cfgClass::FETCHER_TIMEOUT] );
		}
		if (! empty( $opts[$cfgClass::FETCHER_USER_AGENT] )) {
			curl_setopt( $this->_curl, CURLOPT_USERAGENT, $opts[$cfgClass::FETCHER_USER_AGENT] );
		}
		if (isset( $opts[$cfgClass::FETCHER_FOLLOW_LOCATION] )) {
			curl_setopt( $this->_curl, CURLOPT_FOLLOWLOCATION, $opts[$cfgClass::FETCHER_FOLLOW_LOCATION] );
		}
		if (isset( $opts[$cfgClass::FETCHER_MAX_REDIRS] )) {
			curl_setopt( $this->_curl, CURLOPT_MAXREDIRS, $opts[$cfgClass::FETCHER_MAX_REDIRS] );
		}
		curl_setopt( $this->_curl, CURLOPT_SSL_VERIFYPEER, false );
		if (! empty( $opts[$cfgClass::FETCHER_PROXY] )) {
			curl_setopt( $this->_curl, CURLOPT_PROXY, $opts[$cfgClass::FETCHER_PROXY] );
		}
		if (isset( $opts[$cfgClass::FETCHER_FAKE_REFERER] )) {
			curl_setopt( $this->_curl, CURLOPT_REFERER, $this->url );
		}

		$logstr = sprintf(
			'-- fetching url %s, timeout: %s, ua: %s, follow_loc: %s, max_redirs: %s, proxy: %s, fake_ref: %s', 
			$this->url,
			$opts[$cfgClass::FETCHER_TIMEOUT],
			$opts[$cfgClass::FETCHER_USER_AGENT],
			$opts[$cfgClass::FETCHER_FOLLOW_LOCATION] ? 'y' : 'n',
			$opts[$cfgClass::FETCHER_MAX_REDIRS],
			$opts[$cfgClass::FETCHER_PROXY],
			$opts[$cfgClass::FETCHER_FAKE_REFERER] ? 'y' : 'n'
		);
		$this->log( $logstr );

		$fetchStart = microtime( true );
		$content = curl_exec( $this->_curl );
		$httpStatusCode = curl_getinfo( $this->_curl, CURLINFO_HTTP_CODE );
		$contentType = curl_getinfo( $this->_curl, CURLINFO_CONTENT_TYPE );

		$this->log( sprintf( '-- fetched, took: %s', round( (float) microtime( true ) - (float) $fetchStart ), 2 ) );

		$encoding = null;
		$matches = array();
		if (preg_match( '@^.+;\s*charset=(.*)$@', $contentType, $matches )) {
			$encoding = $matches[1];
		}

		if ($content === false || $httpStatusCode !== 200) {
			$excpMsg = sprintf( "Error fetching content from url '%s'%s", $this->url, curl_errno( $this->_curl ) ? ', curl error: ' . curl_error( $this->_curl ) : '' );
			throw new CommunicationException( $excpMsg );
		}

		curl_close( $this->_curl );

		$this->_fetched = true;
		$this->content = $content;
		$this->contentType = $contentType;
		$this->encoding = $encoding;
		return $content;
	} // fetch }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $path
	 * @param	int $perms		optional
	 * @throws	FileReadWriteException
	 * @return	bool
	 */
	public function save( $path, $perms = 0666 )
	{
		if (! $this->_fetched) {
			$this->fetch();
		}

		if (false === @file_put_contents( $path, $this->content(), $perms )) {
			throw new FileReadWriteException( "Error saving contents to file '$path'" );
		}
		$this->log( "Saved file to '$path'" );
		return true;
	} // save }}}


	/**
	 * Enter description here ...
	 *
	 * @return	string
	 */
	public function content()
	{
		if (! $this->_fetched) {
			$this->fetch();
		}
		return $this->content;
	} // content }}}


	/**
	 * Enter description here ...
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return $this->url;
	} // __toString }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $url
	 * @return	string
	 */
	public function sanitize( $url )
	{
		return trim( $url );
	} // sanitize }}}
}
