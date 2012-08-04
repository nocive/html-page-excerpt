<?php

/*************************************************************************************************
 *
 * HTML Page Excerpt
 *
 * PHP library that mimics facebook "share" mechanism, by retrieving an excerpt
 * for the requested url. It currently supports: title, text excerpt, thumbnails and favicon
 *
 * Requires PHP >= 5.3
 *
 * @category HTML Scrapper
 * @package  HTMLPageExcerpt
 * @author   Jose' Pedro Saraiva <nocive _ gmail _ com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version  0.2
 * @link     https://github.com/nocive/html-page-excerpt/
 *
 *************************************************************************************************/

namespace HTMLPageExcerpt;
require_once( __DIR__ . '/Bootstrap/Bootstrap.php' );

class HTMLPageExcerpt extends Base
{
	/**
	 * @constant	string
	 */
	const FIELD_TITLE = 'title';

	/**
	 * @constant	string
	 */
	const FIELD_THUMBS = 'thumbs';

	/**
	 * @constant	string
	 */
	const FIELD_EXCERPT = 'excerpt';
	
	/**
	 * @constant	string
	 */
	const FIELD_FAVICON = 'favicon';

	/**
	 * @constant	string
	 */
	const OPEN_GRAPH_NS = 'og';

	/**
	 * @constant	string
	 */
	const OPEN_GRAPH_NS_URL = 'http://ogp.me/ns#';

	/**
	 * @var		Url
	 * @access	public
	 */
	public $url;

	/**
	 * @var		string
	 * @access	public
	 */
	public $html;

	/**
	 * @var		Text
	 * @access	public
	 */
	public $title;

	/**
	 * @var		Text
	 * @access	public
	 */
	public $excerpt;

	/**
	 * @var		Image
	 * @access	public
	 */
	public $favicon;

	/**
	 * @var		array
	 * @access	public
	 */
	public $thumbnails;

	/**
	 * @var		\DOMDocument
	 * @access	protected
	 */
	protected $_dom;

	/**
	 * @var		\DOMXPath
	 * @access	protected
	 */
	protected $_xpath;

	/**
	 * @var		bool
	 * @access	protected
	 */
	protected $_loaded = false;

	/**
	 * @var		array
	 * @access	protected
	 */
	protected $_fields = array( 
		self::FIELD_TITLE,
		self::FIELD_EXCERPT,
		self::FIELD_THUMBS,
		self::FIELD_FAVICON
	);

	/**
	 * @var		HTMLPageExcerpt
	 * @access	protected
	 */
	protected static $_instance;


	/**
	 * Enter description here ...
	 * 
	 * @param	string $source		optional
	 * @param	array|Config $config	optional
	 */
	public function __construct( $source = null, $config = null )
	{
		parent::__construct( $config );

		Bootstrap::checkRequirements();

		if (null !== $source) {
			$this->load( $source );
		}
	} // __construct }}}

	/**
	 * Enter description here ...
	 *
	 * @return HTMLPageExcerpt
	 */
	public static function getInstance()
	{
		if (! static::$_instance) {
			static::$_instance = new static();
		}
		return static::$_instance;
	} // getInstance }}}

	/**
	 * Enter description here ...
	 *
	 * @param	HTMLPageExcerpt $instance
	 */
	public static function setInstance( $instance )
	{
		if (! $instance instanceof HTMLPageExcerpt) {
			throw new \InvalidArgumentException( '$instance is not an instance of HTMLPageExcerpt' );
		}
		static::$_instance = $instance;
	} // setInstance }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	string $source		optional
	 * @throws	FileReadWriteException
	 */
	public function load( $source = null )
	{
		if ($this->_loaded) {
			$this->reset();
		}

		if ($source !== null) {
			$url = new Url( $source, true, false, $this->_config );
			if ($url->isValid()) {
				$url->fetch();
				$html = $url->content();
				$this->url = $url;
			} else {
				if (false === ($html = @file_get_contents( $source ))) {
					throw new FileReadWriteException( "Error reading file '$source'" );
				}
			}
			$this->loadHTML( $html, null );
		}
	} // load }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	string $html
	 * @param	string $url	optional
	 * @throws	FatalException
	 */
	public function loadHTML( $html, $url = null )
	{
		if ($this->_loaded) {
			$this->reset();
		}

		if ($url !== null) {
			$this->url = new Url( $url, true, false, $this->_config );
			if (! $this->url->isValid()) {
				throw new FatalException( "Url '$url' is not a valid absolute url" );
			}
		} elseif ($this->url === null) {
			throw new FatalException( 'Please provide the source url, this is required to absolutize relative url\'s' );
		}

		$this->_loadDocument( $html );	
	} // loadHTML }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	mixed $fields	string, array or null
	 * @param	bool $flatten
	 * @throws	FatalException
	 * @return	mixed
	 */
	public function get( $fields = '*', $flatten = false )
	{
		if (! $this->_loaded) {
			throw new FatalException( 'You must call load() or loadHTML() before trying to retrieve any information' );
		}

		if (! is_array( $fields )) {
			$fields = array( $fields );
		}

		$getAll = false;
		if ($fields === array( '*' )) {
			$fields = $this->_fields;
			$getAll = true;
		}

		$data = array();
		foreach ( $fields as $f ) {
			if ($getAll || in_array( $f, $this->_fields )) {
				$data[$f] = $this->_find( $f );
				if ($flatten) {
					if (is_array( $data[$f] )) {
						foreach( $data[$f] as &$v ) {
							$v = (string) $v;
						}
					} else {
						$data[$f] = (string) $data[$f];
					}
				}
			}
		}

		return $data;
	} // get }}}

	/**
	 * Resets the class to it's initial state
	 */
	public function reset()
	{
		$this->url = null;
		$this->html = null;
		$this->title = null;
		$this->excerpt = null;
		$this->favicon = null;
		$this->thumbnails = null;
		$this->_dom = null;
		$this->_xpath = null;
		$this->_loaded = false;
	} // reset }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	string $html
	 */
	protected function _loadDocument( $html )
	{
		$config = $this->getConfig();

		$dom = new \DOMDocument();
		$dom->preserveWhitespace = false;
		@$dom->loadHTML( $html );

		$dom->encoding = $this->_getEncoding( $dom );
		$html = $this->_repairHTML( $html, $dom->encoding, $config->get( $config::ENCODING ) );

		@$dom->loadHTML( $html );

		// this must come after loadHTML
		$this->_xpath = new \DOMXPath( $dom );
		$this->_xpath->registerNamespace( static::OPEN_GRAPH_NS, static::OPEN_GRAPH_NS_URL );

		$this->_dom = $dom;
		$this->html = $html;
		$this->_loaded = true;
	} // _loadDocument }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	\DOMDocument $dom
	 * @return	string
	 */
	protected function _getEncoding( &$dom )
	{
		$config = $this->getConfig();

		// get encoding from announced http content type header, if any
		if (! empty( $this->url->encoding )) {
			return $this->url->encoding;
		}

		// fallback to meta http-equiv content-type tag, if no document encoding detected
		if (empty( $dom->encoding )) {
			// if DOMDocument fails to find correct encoding, try to get it from meta tags
			$xpath = new \DOMXPath( $dom );
			$elements = $xpath->query( '/html/head/meta[@http-equiv="Content-Type"]/@content' );
			unset( $xpath );
			$matches = array();
			if ($elements->length === 1 && preg_match( '@^.+;\s*charset=(.*)$@', $elements->item( 0 )->nodeValue, $matches )) {
				return $matches[1];
			}
		}

		// assume default encoding if no encoding found
		return empty( $dom->encoding ) ? $config->get( $config::ENCODING ) : $dom->encoding;
	} // _getEncoding }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	string $html
	 * @param	string $fromEncoding
	 * @param	string $toEncoding
	 * @return	string
	 */
	protected function _repairHTML( $html, $fromEncoding, $toEncoding )
	{
		// hack for http://tvnet.sapo.pt/noticias/detalhes.php?id=68085
		// would it be possible to find a generic solution that replaces all invalid chars with their valid correspondents?
		if (strtoupper( $fromEncoding ) === 'UTF-8') {
			$html = str_replace( array( "\xe1", "\xe3", "\xe7", "\xea" ), array( 'á', 'ã', 'ç', 'ê' ), $html );
		}

		if (! empty( $fromEncoding )) {
			if (strtoupper( $fromEncoding ) !== strtoupper( $toEncoding )) {
				$html = $this->_convertEncoding( $html, $fromEncoding, $toEncoding );
			}
			$html = mb_convert_encoding( $html, 'HTML-ENTITIES', $toEncoding );
		}

		// remove script, style and iframe tags
		$html = preg_replace( '@<\s*\b(script|style|iframe)\b[^>]*>(.*?)<\s*\/\s*(script|style|iframe)\s*>@is', '', $html );

		// replace some utf8 weirdness (probably from MS office cpy&paste)
		$html = str_replace( array( "\xc2\x92", "\xc2\x93", "\xc2\x94" ), array( '&rsquo;', '&ldquo;', '&rdquo;' ), $html);

		// workaround to turn erroneous quotes into correct ones
		$html = str_replace( array( '&#146;', '&#147;', '&#148;', '&#150;' ), array( '&#8217;', '&#8220;', '&#8221;', '&#8722;' ), $html );

		return $html;
	} // _repairHTML


	/**
	 * Enter description here ...
	 * 
	 * @param	string $str
	 * @param	string $from
	 * @param	string $to
	 * @return	string
	 */
	protected function _convertEncoding( $str, $from, $to )
	{
		return @iconv( $from, $to, $str );
	} // _convertEncoding }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $what
	 * @return	mixed
	 */
	protected function _find( $what )
	{
		$what = strtolower( $what );
		if (in_array( $what, $this->_fields )) {
			// return if already cached
			if (isset( $this->{$what} ) && $this->{$what} !== null) {
				return $this->{$what};
			}
			return $this->{'_find' . ucfirst( $what )}();
		}
		return null;
	} // _find }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	Text
	 */
	protected function _findTitle()
	{
		$config = $this->getConfig();

		$title = null;
		$SEO_ignFilters = $config->get( $config::TITLE_SEO_TAGS_IGNORE_FILTERS );

		foreach( $config->get( $config::TITLE_SEARCH_TAGS ) as $tag ) {
			switch ($tag) {
			case 'meta og:title':
				$elements = $this->_xpath->query( '/html/head/meta[@property="og:title"]/@content' );
				foreach( $elements as $elem ) {
					$candidate = new Text( $elem->nodeValue, true, $config );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( static::FIELD_TITLE ) ))) {
						// found
						$title = $candidate;
						break 3;
					}
				}
				break;
			case 'title':
				$elements = $this->_xpath->query( '/html/head/title' );
				foreach( $elements as $elem ) {
					$candidate = new Text( $elem->nodeValue, true, $config );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( static::FIELD_TITLE ) ))) {
						// found
						$title = $candidate;
						break 3;
					}
				}
				break;
			default:
				$elements = $this->_dom->getElementsByTagName( $tag );
				foreach( $elements as $elem ) {
					$candidate = new Text( $elem->nodeValue, true, $config );
					if (! $candidate->isEmpty() && $candidate->matches( $this->_getFilterOpts( static::FIELD_TITLE ) )) {
						// found
						$title = $candidate;
						break 3;
					}
				}
			} // end switch
		} // end foreach

		if ($config->get( $config::TITLE_TRUNCATE ) && $title instanceof Text) {
			$title->truncate( $config->get( $config::TITLE_TRUNCATE_LENGTH ), $config->get( $config::TITLE_TRUNCATE_TERMINATOR ) );
		}
		$this->title = $title;
		return $title;
	} // _findTitle }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	Text
	 */
	protected function _findExcerpt()
	{
		$config = $this->getConfig();

		$excerpt = null;
		$SEO_ignFilters = $config->get( $config::EXCERPT_SEO_TAGS_IGNORE_FILTERS );

		foreach ( $config->get( $config::EXCERPT_SEARCH_TAGS ) as $tag ) {
			switch ($tag) {
			case 'meta og:description':
				// method: search <meta property="og:description" content="" />
				// try to find a meta tag with property og:description as according to
				// open graph protocol (@see http://developers.facebook.com/docs/opengraph/)
				$elements = $this->_xpath->query( '/html/head/meta[@property="og:description"]/@content' );
				foreach ( $elements as $elem ) {
					$candidate = new Text( $elem->nodeValue, true, $this->_config );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( static::FIELD_EXCERPT ) ))) {
						// found
						$excerpt = $candidate;
						break 3;
					}
				}
				break;
			case 'meta description':
				// method: search <meta name="description" content="" />
				$elements = $this->_xpath->query( '/html/head/meta[@name="description"]/@content' );
				foreach ( $elements as $elem ) {
					$candidate = new Text( $elem->nodeValue, true, $this->_config );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( static::FIELD_EXCERPT ) ))) {
						// found
						$excerpt = $candidate;
						break 3;
					}
				}
				break;
			case 'article section':
				// new html5 method
				$elements = $this->_xpath->query( '/html/body//article/section' );
				foreach ( $elements as $elem ) {
					$candidate = new Text( $elem->nodeValue, true, $this->_config );
					if ($candidate->matches( $this->_getFilterOpts( static::FIELD_EXCERPT ) )) {
						// found
						$excerpt = $candidate;
						break 3;
					}
				}
				break;
			default:
				// default behaviour: search "normal" tags for text
				$elements = $this->_dom->getElementsByTagName( $tag );
				foreach ( $elements as $elem ) {
					$candidate = new Text( Util::DOMinnerHTML( $elem ), true, $this->_config );
					if ($candidate->matches( $this->_getFilterOpts( static::FIELD_EXCERPT ) )) {
						// found
						$excerpt = $candidate;
						break 3;
					}
				}
			} // end switch
		} // end foreach

		if ($excerpt instanceof Text) {
			if ($config->get( $config::EXCERPT_TRUNCATE )) {
				$excerpt->truncate( $config->get( $config::EXCERPT_TRUNCATE_LENGTH ), $config->get( $config::EXCERPT_TRUNCATE_TERMINATOR ) );
			}
			if ($config->get( $config::EXCERPT_LINKIFY )) {
				$excerpt->linkify();
			}
		}
		$this->excerpt = $excerpt;
		return $excerpt;
	} // _findExcerpt }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	array
	 */
	protected function _findThumbs()
	{
		$config = $this->getConfig();

		$SEO_ignFilters = $config->get( $config::THUMBS_SEO_TAGS_IGNORE_FILTERS );
		$thumbnails = array();
		$tries = 0;

		// first method: search <meta propert="og:image" content="">
		$elements = $this->_xpath->query( '/html/head/meta[@property="og:image"]/@content' );
		foreach ( $elements as $elem ) {
			$candidate = new Image( $elem->nodeValue, false, $this->_config );
			$candidate->url->absolutize( (string) $this->url );

			// blacklisting check shouldn't be necessary for og:image
			// just check if it's a valid url
			if (! $candidate->url->isValid()) {
				continue;
			}

			try {
				$candidate->identify();
				if ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( static::FIELD_THUMBS ) )) {
					$thumbnails[] = $candidate;
					break;
				}
			} catch ( InvalidImageFileException $e ) {
			} catch ( CommunicationException $e ) {
			}

			$tries ++;
			if ($tries > $config->get( $config::THUMBS_MAX_TRIES )) {
				break;
			}
		}

		// only try other methods if og:image was not found or was invalid
		if (empty( $thumbnails )) {
			$thumbs = array();
			// second method: search for found img tags
			$elements = $this->_xpath->query( '/html/body//img/@src' );
			foreach ( $elements as $elem ) {
				$candidate = new Image( $elem->nodeValue, false, $this->_config );
				$candidate->url->absolutize( (string) $this->url );

				if (! $candidate->url->isValid() || $this->_isUrlBlacklisted( static::FIELD_THUMBS, $candidate->url )) {
					continue;
				}
				$thumbs[] = $candidate;
			}

			// all Url class instances will be converted to string after calling array_unique
			$thumbs = array_unique( $thumbs );

			// we should validate after array_unique, otherwise we could be wasting a lot of resources on repeated url's
			foreach ( $thumbs as $t ) {
				try {
					$t->identify();
					if ($t->matches( $this->_getFilterOpts( static::FIELD_THUMBS ) )) {
						$thumbnails[] = $t;
						if (count( $thumbnails ) >= $config->get( $config::THUMBS_FOUND_STOP_COUNT )) {
							break;
						}
					}
				} catch ( InvalidImageFileException $e ) {
				} catch ( CommunicationException $e ) {
				}

				$tries ++;
				if ($tries > $config->get( $config::THUMBS_MAX_TRIES )) {
					break;
				}
			}
		}

		$this->thumbnails = $thumbnails;
		return $this->thumbnails;
	} // _findThumbnails }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	Image
	 */
	protected function _findFavicon()
	{
		$favicon = null;

		// first try to find any <link rel="icon"> tags for an "announced" favicon
		$elements = $this->_xpath->query( '/html/head/link[@rel="icon"]/@href' );
		foreach ( $elements as $elem ) {
			$candidate = new Image( $elem->nodeValue, false, $this->_config );
			$candidate->url->absolutize( (string) $this->url );

			if (! $this->url->isValid()) {
				continue;
			}

			try {
				$candidate->identify();
				if ($candidate->matches( $this->_getFilterOpts( static::FIELD_FAVICON ) )) {
					// found
					$favicon = $candidate;
					break;
				}
			} catch ( InvalidImageFileException $e ) {
			} catch ( CommunicationException $e ) {
			}
		}

		// if nothing was found, look for it in the default location http://domain/favicon.ico
		if (empty( $favicon )) {
			$defaultFavicon = parse_url( $this->url, PHP_URL_SCHEME ) . '://' . parse_url( $this->url, PHP_URL_HOST ) . '/favicon.ico';

			try {
				$candidate = new Image( $defaultFavicon, true, $this->_config );
				if ($candidate->matches( $this->_getFilterOpts( static::FIELD_FAVICON ) )) {
					// found
					$favicon = $candidate;
				}
			} catch ( InvalidImageFileException $e ) {
			} catch ( CommunicationException $e ) {
			}
		}

		$this->favicon = $favicon;
		return $this->favicon;
	} // _findFavicon }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $type
	 * @param	string $url
	 * @return	bool
	 */
	protected function _isUrlBlacklisted( $type, $url )
	{
		$config = $this->getConfig();

		$type = strtoupper( $type );
		$constantName = get_class( $config ) . "::{$type}_URL_BLACKLIST";
		$blacklistPattern = $config->get( constant( $constantName ) );
		if (empty( $blacklistPattern )) {
			return false;
		}

		$blacklistPattern = '@^' . str_replace( '@', '\@', implode( '|', $blacklistPattern ) ) . '$@i';
		return (bool) preg_match( $blacklistPattern, (string) $url );
	} // _isUrlBlacklisted }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $type
	 * @return	array
	 */
	protected function _getFilterOpts( $type )
	{
		$config = $this->getConfig();

		switch ($type) {
		case static::FIELD_TITLE:
			return array( 
				'min_length' => $config->get( $config::TITLE_MIN_LENGTH ), 
				'max_length' => $config->get( $config::TITLE_MAX_LENGTH ) 
			);
		case static::FIELD_EXCERPT:
			return array( 
				'min_length' => $config->get( $config::EXCERPT_MIN_LENGTH ), 
				'max_length' => $config->get( $config::EXCERPT_MAX_LENGTH ) 
			);
		case static::FIELD_THUMBS:
			return array( 
				'mimetypes_include' => $config->get( $config::THUMBS_MIMETYPES_INCLUDE ), 
				'mimetypes_exclude' => $config->get( $config::THUMBS_MIMETYPES_EXCLUDE ), 
				'extensions_include' => $config->get( $config::THUMBS_EXTENSIONS_INCLUDE ), 
				'extensions_exclude' => $config->get( $config::THUMBS_EXTENSIONS_EXCLUDE ), 
				'min_width' => $config->get( $config::THUMBS_MIN_WIDTH ), 
				'max_width' => $config->get( $config::THUMBS_MAX_WIDTH ), 
				'min_height' => $config->get( $config::THUMBS_MIN_HEIGHT ), 
				'max_height' => $config->get( $config::THUMBS_MAX_HEIGHT ), 
				'min_size' => $config->get( $config::THUMBS_MIN_SIZE ), 
				'max_size' => $config->get( $config::THUMBS_MAX_SIZE ) 
			);
		case static::FIELD_FAVICON:
			return array( 
				'mimetypes_include' => $config->get( $config::FAVICON_MIMETYPES_INCLUDE ), 
				'mimetypes_exclude' => $config->get( $config::FAVICON_MIMETYPES_EXCLUDE ), 
				'extensions_include' => $config->get( $config::FAVICON_EXTENSIONS_INCLUDE ), 
				'extensions_exclude' => $config->get( $config::FAVICON_EXTENSIONS_EXCLUDE ), 
				'min_width' => $config->get( $config::FAVICON_MIN_WIDTH ), 
				'max_width' => $config->get( $config::FAVICON_MAX_WIDTH ), 
				'min_height' => $config->get( $config::FAVICON_MIN_HEIGHT ), 
				'max_height' => $config->get( $config::FAVICON_MAX_HEIGHT ), 
				'min_size' => $config->get( $config::FAVICON_MIN_SIZE ), 
				'max_size' => $config->get( $config::FAVICON_MAX_SIZE ) 
			);
		}
	} // _getFilterOpts }}}
}
