<?php

// @TODO logging

define( 'HTML_PAGEEXCERPT_PATH', dirname( __FILE__ ) );
define( 'HTML_PAGEEXCERPT_LOGPATH', HTML_PAGEEXCERPT_PATH );
define( 'HTML_PAGEEXCERPT_LOGFILE', HTML_PAGEEXCERPT_LOGPATH . DIRECTORY_SEPARATOR . 'html_pageexcerpt.log' );

  

/**************************************
 * 
 * Settings class
 * 
 **************************************/
class HTML_PageExcerpt_Settings
{
	/**
	 * @var array
	 * @access public
	 */
	public static $settings = array( 
			'logging' => false, 
			'logfile' => HTML_PAGEEXCERPT_LOGFILE, 
			'encoding' => 'UTF-8', 
			'mimetypes_map_path' => '/etc/mime.types', 
			
			'fetcher_proxy' => '', 
			//'fetcher_proxy' => 'http://10.135.32.7:3128',
			'fetcher_timeout' => 30, 
			'fetcher_follow_location' => true, 
			'fetcher_max_redirs' => 2, 
			'fetcher_user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.71 Safari/534.24', 
			'fetcher_fake_referer' => true, 

			// title settings
			'title_search_tags' => array(
					'meta og:title',
					'title',
					'h1'
			),
			'title_seo_tags_ignore_filters' => true,
			'title_min_length' => 0,
			'title_max_length' => 0,
			'title_truncate' => false,
			'title_truncate_length' => 0,
			'title_truncate_terminator' => ' ...',
			
			// excerpt settings
			'excerpt_search_tags' => array( 
					'meta og:description', 
					'meta description', 
					'p' 
			), 
			'excerpt_seo_tags_ignore_filters' => true,
			'excerpt_min_length' => 50, 
			'excerpt_max_length' => 0, 
			'excerpt_truncate' => true, 
			'excerpt_truncate_length' => 320, 
			'excerpt_truncate_terminator' => ' ...', 
			'excerpt_linkify' => true,
			
			// thumbnails settings
			'thumbnails_seo_tags_ignore_filters' => false,
			'thumbnails_stop_on_first_found' => false, 
			// TODO thumbnails_found_stop_count instead of the above
			'thumbnails_min_width' => 100, 
			'thumbnails_min_height' => 100, 
			'thumbnails_max_width' => 250, 
			'thumbnails_max_height' => 250, 
			'thumbnails_max_tries' => 10, 
			'thumbnails_min_size' => 0, 
			'thumbnails_max_size' => 0, 
			'thumbnails_mimetypes_include' => array( 
					'image/jpeg', 
					'image/png', 
					'image/x-ms-bmp', 
					'image/gif' 
			), 
			'thumbnails_mimetypes_exclude' => array(), 
			'thumbnails_extensions_include' => array(), 
			'thumbnails_extensions_exclude' => array(), 
			'thumbnails_url_blacklist' => array( 
					'da\.feedsportal\.com.*', 
					'.*commindo-media-ressourcen\.de.*',  // smashing magazine ads
					'api\.tweetmeme\.com.*', 
					'.*wp-digg-this.*', 
					'.*doubleclick\.net.*' 
			), 
			
			// favicon settings
			'favicon_min_width' => 16, 
			'favicon_min_height' => 16, 
			'favicon_max_width' => 0, 
			'favicon_max_height' => 0, 
			'favicon_max_tries' => 10, 
			'favicon_min_size' => 0, 
			'favicon_max_size' => 0, 
			'favicon_mimetypes_include' => array( 
					'image/x-icon', 
					'image/jpeg', 
					'image/png', 
					'image/x-ms-bmp', 
					'image/gif' 
			), 
			'favicon_mimetypes_exclude' => array(), 
			'favicon_extensions_include' => array(), 
			'favicon_extensions_exclude' => array() 
	);
	
	/**
	 * @var array
	 * @access public
	 */
	public static $extensions = array( 
			'curl', 
			'DOM', 
			'iconv', 
			//'tidy' 
	);
	
	/**
	 * @var array
	 * @access public
	 */
	public static $dependencies = array( 
			'MIME/Type.php' 
	);
	

	/**
	 * Enter description here ...
	 * 
	 * @param null|string $setting
	 * @param mixed $value
	 * @throws HTML_PageExcerpt_InvalidArgumentException
	 */
	public static function set( $setting = null, $value )
	{
		if ($setting === null) {
			if (! is_array( $value )) {
				throw new HTML_PageExcerpt_InvalidArgumentException( 'Second argument must be an array when first argument is null' );
			}
			self::$settings = array_merge( self::$settings, $value );
		} else {
			self::$settings[$setting] = $value;
		}
	} // set }}}


	/**
	 * Enter description here ...
	 * 
	 * @param [ null|string $setting ]
	 * @return mixed
	 */
	public static function get( $setting = null )
	{
		if ($setting === null) {
			return self::$settings;
		}
		return isset( self::$settings[$setting] ) ? self::$settings[$setting] : null;
	} // get }}}


	/**
	 * Enter description here ...
	 * 
	 * @throws HTML_PageExcerpt_FatalException
	 */
	public static function checkRequirements()
	{
		foreach ( self::$extensions as $ext ) {
			if (! extension_loaded( $ext )) {
				throw new HTML_PageExcerpt_FatalException( "Required extension '$ext' is not loaded!" );
			}
		}
		
		foreach ( self::$dependencies as $dep ) {
			if (false === @include_once ($dep)) {
				throw new HTML_PageExcerpt_FatalException( "Required dependency '$dep' was not found!" );
			}
		}
	} // checkRequirements }}}
}

/**************************************
 * 
 * Main class
 * 
 **************************************/
class HTML_PageExcerpt extends HTML_PageExcerpt_Object
{
	/**
	 * @var HTML_PageExcerpt_Url
	 * @access public
	 */
	public $url;
	
	/**
	 * @var string
	 * @access public
	 */
	public $html;
	
	/**
	 * @var HTML_PageExcerpt_Text
	 * @access public
	 */
	public $title;
	
	/**
	 * @var HTML_PageExcerpt_Text
	 * @access public
	 */
	public $excerpt;
	
	/**
	 * @var HTML_PageExcerpt_Image
	 * @access public
	 */
	public $favicon;
	
	/**
	 * @var array
	 * @access public
	 */
	public $thumbnails;
	
	/**
	 * @var DOMDocument
	 * @access protected
	 */
	protected $_dom;
	
	/**
	 * @var DOMXPath
	 * @access protected
	 */
	protected $_xpath;
	
	/**
	 * @var bool
	 * @access protected
	 */
	protected $_loaded = false;
	
	/**
	 * @var array
	 * @access protected
	 */
	protected $_fields = array( 
			'title',
			'excerpt', 
			'thumbnails', 
			'favicon' 
	);


	/**
	 * Enter description here ...
	 * 
	 * @param [ string $source ]
	 * @param [ array $settings ]
	 */
	public function __construct( $source = null, $settings = null )
	{
		static $requirementsChecked = false;

		if (! $requirementsChecked) {
			HTML_PageExcerpt_Settings::checkRequirements();
			$requirementsChecked = true;
		}

		if ($settings !== null) {
			$this->config( null, $settings );
		}
		if ($source !== null) {
			$this->load( $source );
		}
	} // __construct }}}


	/**
	 * Enter description here ...
	 * 
	 * @param [ string $setting ]
	 * @throws HTML_PageExcerpt_FileReadWriteException
	 */
	public function load( $source = null )
	{
		if ($this->_loaded) {
			$this->reset();
		}

		if ($source !== null) {
			$url = new HTML_PageExcerpt_Url( $source );
			if ($url->isValid()) {
				$url->fetch();
				$html = $url->content();
				$this->url = $url;
			} else {
				if (false === ($html = @file_get_contents( $source ))) {
					throw new HTML_PageExcerpt_FileReadWriteException( "Error reading file '$source'" );
				}
			}
			$this->loadHTML( $html, null );
		}
	} // load }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $html
	 * @param [ string $url ]
	 * @throws HTML_PageExcerpt_FatalException
	 */
	public function loadHTML( $html, $url = null )
	{
		if ($this->_loaded) {
			$this->reset();
		}

		if ($url !== null) {
			$this->url = new HTML_PageExcerpt_Url( $url );
			if (! $this->url->isValid()) {
				throw new HTML_PageExcerpt_FatalException( "Url '$url' is not a valid absolute url" );
			}
		} elseif ($this->url === null) {
			throw new HTML_PageExcerpt_FatalException( 'Please provide the source url, this is required to absolutize relative url\'s' );
		}
	
		$this->_loadDocument( $html );	
	} // loadHTML }}}


	/**
	 * Enter description here ...
	 * 
	 * @param null|string $setting
	 * @param mixed $value
	 */
	public function config( $setting = null, $value )
	{
		return HTML_PageExcerpt_Settings::set( $setting, $value );
	} // config }}}


	/**
	 * Enter description here ...
	 * 
	 * @param null|string|array $fields
	 * @return mixed
	 * @throws HTML_PageExcerpt_FatalException
	 */
	public function get( $fields = '*' )
	{
		if (! $this->_loaded) {
			throw new HTML_PageExcerpt_FatalException( 'You must call load() or loadHTML() before trying to retrieve any information' );
		}
		
		if (! is_array( $fields )) {
			$fields = array( 
					$fields 
			);
		}
		
		$getAll = false;
		if ($fields === array( 
				'*' 
		)) {
			$fields = $this->_fields;
			$getAll = true;
		}
		
		$data = array();
		foreach ( $fields as $f ) {
			if ($getAll || in_array( $f, $this->_fields )) {
				$data[$f] = $this->_find( $f );
			}
		}
		
		return $data;
	} // get }}}


	/**
	 * Enter description here ...
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
	 * @param string $html
	 */
	protected function _loadDocument( $html )
	{
		$dom = new DOMDocument();
		$dom->preserveWhitespace = false;
		@$dom->loadHTML( $html );
	 
		$dom->encoding = $this->_getDOMEncoding( $dom );
		$html = $this->_repairHTML( $html, $dom->encoding, HTML_PageExcerpt_Settings::get( 'encoding' ) );
		
		@$dom->loadHTML( $html );

		// this must come after loadHTML
		$this->_xpath = new DOMXPath( $dom );
		$this->_xpath->registerNamespace( 'og', 'http://ogp.me/ns#' );

		$this->_dom = $dom;
		$this->html = $html;
		$this->_loaded = true;
	} // _loadDocument }}}


	/**
	 * Enter description here ...
	 * 
	 * @param DOMDocument $dom
	 * @return string
	 */
	protected function _getDOMEncoding( &$dom )
	{
		// get encoding from announced http content type header, if any
		if (! empty( $this->url->encoding )) {
			return $this->url->encoding;
		}

		// fallback to meta http-equiv content-type tag, if no document encoding detected
		if (empty( $dom->encoding )) {
			// if DOMDocument fails to find correct encoding, try to get it from meta tags
			$xpath = new DOMXPath( $dom );
			$elements = $xpath->query( '/html/head/meta[@http-equiv="Content-Type"]/@content' );
			$matches = array();
			if ($elements->length === 1 && preg_match( '@^.+;\s*charset=(.*)$@', $elements->item( 0 )->nodeValue, $matches )) {
				return $matches[1];
			}
		}
		
		// assume default encoding if no encoding found
		return empty( $dom->encoding ) ? HTML_PageExcerpt_Settings::get( 'encoding' ) : $dom->encoding;
	} // _getDOMEncoding }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $html
	 * @param string $fromEncoding
	 * @param string $toEncoding
	 * @return string
	 */
	protected function _repairHTML( $html, $fromEncoding, $toEncoding )
	{
		// hack for http://tvnet.sapo.pt/noticias/detalhes.php?id=68085
		// can we find a generic solution that replaces all invalid chars with their valid correspondents?
		if (strtoupper( $fromEncoding ) === 'UTF-8') {
			$html = str_replace( array(
				"\xe1", 
				"\xe3", 
				"\xe7", 
				"\xea" ), array( 
				'á', 
				'ã', 
				'ç', 
				'ê' ), $html );
		}

		if (! empty( $fromEncoding )) {
			if (strtoupper( $fromEncoding ) !== strtoupper( $toEncoding )) {
				$html = $this->_convertEncoding( $html, $fromEncoding, $toEncoding );
			}
			$html = mb_convert_encoding( $html, 'HTML-ENTITIES', $toEncoding );
		}

		// remove script and style tags
		$html = preg_replace( '@<\s*\b(script|style)\b[^>]*>(.*?)<\s*\/\s*(script|style)\s*>@is', '', $html );

		// replace some utf8 weirdness (probably from office cpy&paste)
		$html = str_replace( array(
				"\xc2\x92", 
				"\xc2\x93",
				"\xc2\x94",
			), array(
				'&rsquo;', 
				'&ldquo;', 
				'&rdquo;',
			), $html);

		
		// workaround to turn erroneous quotes into correct ones
		$html = str_replace( array( '&#146;', '&#147;', '&#148;', '&#150;' ), array( '&#8217;', '&#8220;', '&#8221;', '&#8722;' ), $html );

		/*
		// run tidy on html to get a clean version
		$tidy = new tidy();
		// tidy only accepts 'utf8' and html_entity_decode only accepts 'utf-8', hence the str_replace
		$html = $tidy->repairString( $html, array( 
				'wrap' => 0 
		), str_replace( '-', '', $toEncoding ) );
		*/

		return $html;
	} // _repairHTML


	/**
	 * Enter description here ...
	 * 
	 * @param string $str
	 * @param string $from
	 * @param string $to
	 * @return string
	 */
	protected function _convertEncoding( $str, $from, $to )
	{
		return @iconv( $from, $to, $str );
	} // _convertEncoding }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $what
	 * @return mixec
	 */
	protected function _find( $what )
	{
		$what = strtolower( $what );
		if (in_array( $what, $this->_fields )) {
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
	 * @return HTML_PageExcerpt_Text
	 */
	protected function _findTitle()
	{
		$title = null;
		$SEO_ignFilters = HTML_PageExcerpt_Settings::get( 'title_seo_tags_ignore_filters' );

		foreach( HTML_PageExcerpt_Settings::get( 'title_search_tags' ) as $tag ) {
			switch ($tag) {
			case 'meta og:title':
				$elements = $this->_xpath->query( '/html/head/meta[@property="og:title"]/@content' );
				foreach( $elements as $elem ) {
					$candidate = new HTML_PageExcerpt_Text( $elem->nodeValue );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( 'title' ) ))) {
						// found
						$title = $candidate;
						break 3;
					}
				}
				break;
			case 'title':
				$elements = $this->_xpath->query( '/html/head/title' );
				foreach( $elements as $elem ) {
					$candidate = new HTML_PageExcerpt_Text( $elem->nodeValue );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( 'title' ) ))) {
						// found
						$title = $candidate;
						break 3;
					}
				}
				break;
			default:
				$elements = $this->_dom->getElementsByTagName( $tag );
				foreach( $elements as $elem ) {
					$candidate = new HTML_PageExcerpt_Text( $elem->nodeValue );
					if (! $candidate->isEmpty() && $candidate->matches( $this->_getFilterOpts( 'title' ) )) {
						// found
						$title = $candidate;
						break 3;
					}
				}
			} // end switch
		} // end foreach
		if (HTML_PageExcerpt_Settings::get( 'title_truncate' ) && $excerpt instanceof HTML_PageExcerpt_Text) {
			$excerpt->truncate( HTML_PageExcerpt_Settings::get( 'title_truncate_length' ), HTML_PageExcerpt_Settings::get( 'title_truncate_terminator' ) );
		}
		$this->title = $title;
		return $title;
	} // _findTitle }}}


	/**
	 * Enter description here ...
	 * 
	 * @return HTML_PageExcerpt_Text
	 */
	protected function _findExcerpt()
	{
		$excerpt = null;
		$SEO_ignFilters = HTML_PageExcerpt_Settings::get( 'excerpt_seo_tags_ignore_filters' );

		foreach ( HTML_PageExcerpt_Settings::get( 'excerpt_search_tags' ) as $tag ) {
			switch ($tag) {
			case 'meta og:description':
				// method: search <meta property="og:description" content="" />
				// try to find a meta tag with property og:description as according to
				// open graph protocol (@see http://developers.facebook.com/docs/opengraph/)
				$elements = $this->_xpath->query( '/html/head/meta[@property="og:description"]/@content' );
				foreach ( $elements as $elem ) {
					$candidate = new HTML_PageExcerpt_Text( $elem->nodeValue );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( 'excerpt' ) ))) {
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
					$candidate = new HTML_PageExcerpt_Text( $elem->nodeValue );
					if (! $candidate->isEmpty() && ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( 'excerpt' ) ))) {
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
					$candidate = new HTML_PageExcerpt_Text( HTML_PageExcerpt_Utils::DOMinnerHTML( $elem ) );
					if ($candidate->matches( $this->_getFilterOpts( 'excerpt' ) )) {
						// found
						$excerpt = $candidate;
						break 3;
					}
				}
			} // end switch
		} // end foreach
		
		if ($excerpt instanceof HTML_PageExcerpt_Text) {
			if (HTML_PageExcerpt_Settings::get( 'excerpt_truncate' )) {
				$excerpt->truncate( HTML_PageExcerpt_Settings::get( 'excerpt_truncate_length' ), HTML_PageExcerpt_Settings::get( 'excerpt_truncate_terminator' ) );
			}
			if (HTML_PageExcerpt_Settings::get( 'excerpt_linkify' )) {
				$excerpt->linkify();
			}
		}
		$this->excerpt = $excerpt;
		return $excerpt;
	} // _findExcerpt }}}


	/**
	 * Enter description here ...
	 * 
	 * @return array
	 */
	protected function _findThumbnails()
	{
		$SEO_ignFilters = HTML_PageExcerpt_Settings::get( 'thumbnails_seo_tags_ignore_filters' );
		$thumbnails = array();
		$tries = 0;
		
		// first method: search <meta propert="og:image" content="">
		$elements = $this->_xpath->query( '/html/head/meta[@property="og:image"]/@content' );
		foreach ( $elements as $elem ) {
			$candidate = new HTML_PageExcerpt_Image( $elem->nodeValue );
			$candidate->url->absolutize( $this->url );
			
			// blacklisting check shouldn't be necessary for og:image
			// just check if it's a valid url
			if (! $candidate->url->isValid()) {
				continue;
			}
			
			try {
				$candidate->identify();
				if ($SEO_ignFilters || $candidate->matches( $this->_getFilterOpts( 'thumbnails' ) )) {
					$thumbnails[] = $candidate;
					break;
				}
			} catch ( HTML_PageExcerpt_InvalidImageFileException $e ) {
			} catch ( HTML_PageExcerpt_CommunicationException $e ) {
			}
			
			$tries ++;
			if ($tries > HTML_PageExcerpt_Settings::get( 'thumbnails_max_tries' )) {
				break;
			}
		}
		
		// only try other methods if og:image was not found or was invalid
		if (empty( $thumbnails )) {
			$thumbs = array();
			// second method: search for found img tags
			$elements = $this->_xpath->query( '/html/body//img/@src' );
			foreach ( $elements as $elem ) {
				$candidate = new HTML_PageExcerpt_Image( $elem->nodeValue );
				$candidate->url->absolutize( $this->url );
				
				if (! $candidate->url->isValid() || $this->_isUrlBlacklisted( 'thumbnails', $this->url )) {
					continue;
				}
				$thumbs[] = $candidate;
			}
			
			$thumbs = array_unique( $thumbs );

			// we should validate after array_unique, otherwise we could be wasting a lot of resources on repeated url's
			foreach ( $thumbs as $t ) {
				try {
					$t->identify();
					if ($t->matches( $this->_getFilterOpts( 'thumbnails' ) )) {
						$thumbnails[] = $t;
						if (HTML_PageExcerpt_Settings::get( 'thumbnails_stop_on_first_found' )) {
							break;
						}
					}
				} catch ( HTML_PageExcerpt_InvalidImageFileException $e ) {
				} catch ( HTML_PageExcerpt_CommunicationException $e ) {
				}
				
				$tries ++;
				if ($tries > HTML_PageExcerpt_Settings::get( 'thumbnails_max_tries' )) {
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
	 * @return HTML_PageExcerpt_Image
	 */
	protected function _findFavicon()
	{
		$favicon = null;
		
		// first try to find any <link rel="icon"> tags for an "announced" favicon
		$elements = $this->_xpath->query( '/html/head/link[@rel="icon"]/@href' );
		foreach ( $elements as $elem ) {
			$candidate = new HTML_PageExcerpt_Image( $elem->nodeValue );
			$candidate->url->absolutize( $this->url );
			
			if (! $this->url->isValid()) {
				continue;
			}
			
			try {
				$candidate->identify();
				if ($candidate->matches( $this->_getFilterOpts( 'favicon' ) )) {
					// found
					$favicon = $candidate;
					break;
				}
			} catch ( HTML_PageExcerpt_InvalidImageFileException $e ) {
			} catch ( HTML_PageExcerpt_CommunicationException $e ) {
			}
		}
		
		// if nothing was found, look for it in the default location http://domain/favicon.ico
		if (empty( $favicon )) {
			$defaultFavicon = parse_url( $this->url, PHP_URL_SCHEME ) . '://' . parse_url( $this->url, PHP_URL_HOST ) . '/favicon.ico';
			
			try {
				$candidate = new HTML_PageExcerpt_Image( $defaultFavicon, true );
				if ($candidate->matches( $this->_getFilterOpts( 'favicon' ) )) {
					// found
					$favicon = $candidate;
				}
			} catch ( HTML_PageExcerpt_InvalidImageFileException $e ) {
			} catch ( HTML_PageExcerpt_CommunicationException $e ) {
			}
		}
		
		$this->favicon = $favicon;
		return $this->favicon;
	} // _findFavicon }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $type
	 * @param string $url
	 * @return bool
	 */
	protected function _isUrlBlacklisted( $type, $url )
	{
		$blacklistPattern = HTML_PageExcerpt_Settings::get( "{$type}_url_blacklist" );
		if (empty( $blacklistPattern )) {
			return false;
		}
		$blacklistPattern = '@^' . str_replace( '@', '\@', implode( '|', $blacklistPattern ) ) . '$@i';
		return (bool) preg_match( $blacklistPattern, (string) $url );
	} // _isUrlBlacklisted }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $type
	 */
	protected function _getFilterOpts( $type )
	{
		switch ($type) {
		case 'title':
			return array( 
					'min_length' => HTML_PageExcerpt_Settings::get( 'title_min_length' ), 
					'max_length' => HTML_PageExcerpt_Settings::get( 'title_max_length' ) 
			);
		case 'excerpt':
			return array( 
					'min_length' => HTML_PageExcerpt_Settings::get( 'excerpt_min_length' ), 
					'max_length' => HTML_PageExcerpt_Settings::get( 'excerpt_max_length' ) 
			);
		case 'thumbnails':
			return array( 
					'mimetypes_include' => HTML_PageExcerpt_Settings::get( 'thumbnails_mimetypes_include' ), 
					'mimetypes_exclude' => HTML_PageExcerpt_Settings::get( 'thumbnails_mimetypes_exclude' ), 
					'extensions_include' => HTML_PageExcerpt_Settings::get( 'thumbnails_extensions_include' ), 
					'extensions_exclude' => HTML_PageExcerpt_Settings::get( 'thumbnails_extensions_exclude' ), 
					'min_width' => HTML_PageExcerpt_Settings::get( 'thumbnails_min_width' ), 
					'max_width' => HTML_PageExcerpt_Settings::get( 'thumbnails_max_width' ), 
					'min_height' => HTML_PageExcerpt_Settings::get( 'thumbnails_min_height' ), 
					'max_height' => HTML_PageExcerpt_Settings::get( 'thumbnails_max_height' ), 
					'min_size' => HTML_PageExcerpt_Settings::get( 'thumbnails_min_size' ), 
					'max_size' => HTML_PageExcerpt_Settings::get( 'thumbnails_max_size' ) 
			);
		case 'favicon':
			return array( 
					'mimetypes_include' => HTML_PageExcerpt_Settings::get( 'favicon_mimetypes_include' ), 
					'mimetypes_exclude' => HTML_PageExcerpt_Settings::get( 'favicon_mimetypes_exclude' ), 
					'extensions_include' => HTML_PageExcerpt_Settings::get( 'favicon_extensions_include' ), 
					'extensions_exclude' => HTML_PageExcerpt_Settings::get( 'favicon_extensions_exclude' ), 
					'min_width' => HTML_PageExcerpt_Settings::get( 'favicon_min_width' ), 
					'max_width' => HTML_PageExcerpt_Settings::get( 'favicon_max_width' ), 
					'min_height' => HTML_PageExcerpt_Settings::get( 'favicon_min_height' ), 
					'max_height' => HTML_PageExcerpt_Settings::get( 'favicon_max_height' ), 
					'min_size' => HTML_PageExcerpt_Settings::get( 'favicon_min_size' ), 
					'max_size' => HTML_PageExcerpt_Settings::get( 'favicon_max_size' ) 
			);
		}
	} // _getFilterOpts }}}


	/**
	 * Enter description here ...
	 * 
	 * @param [ array $urls ]
	 */
	public static function runTests( $urls = null )
	{
		$config = null;
		//$config = array( 'fetcher_user_agent' => 'fake' );
		$instance = isset( $this ) ? $this : new HTML_PageExcerpt( null, $config );
		
		$_urls = array( 
				'http://vimeo.com/24997574', 
				'http://circuspt.blogs.sapo.pt/276442.html', 
				'http://www.publico.pt/Tecnologia/nova-legislacao-para-combate-a-pirataria-no-prazo-maximo-de-um-ano_1500632', 
				'http://www.youtube.com/watch?v=EmLHOGT0v4c&feature=related', 
				'http://photosynth.net/', 
				'http://www.almadeviajante.com/travelnews/005273.php', 
				'http://www.dn.pt/inicio/opiniao/interior.aspx?content_id=1882957&seccao=Jo%E3o+C%E9sar+das+Neves&tag=Opini%E3o+-+Em+Foco&page=-1', 
				'http://cavalinhoselvagem.blogspot.com/2011/07/aniversariantes-de-julho.html', 
				'http://mfmodafeminina.blogs.sapo.pt/21640.html', 
				'http://enfermagemnopc.blogs.sapo.pt/707.html', 
				'http://alentejomagazine.com/2006/02/632/', 
				'http://www.noticiasdevilareal.com/noticias/index.php?action=getDetalhe&id=762', 
				'http://pplware.sapo.pt/windows/software/auslogics-duplicate-file-finder-remova-ficheiros-repetidos/', 
				'http://musica.sapo.pt/noticias/kaossilator_-_sintetizador_de_bolso', 
				'http://www.setubalnarede.pt/content/index.php?action=articlesDetailFo&rec=9255', 
				'http://www.ionline.pt/conteudo/7749-europeias-ferreira-leite-apela-ao-dever-civico-que-todos-tem-votar',
				'http://www.sodoliva.com',
				'http://www.sabado.pt/Actualidade/Especial-Europeias/Blogues.aspx',
				'https://www.sugarsync.com/',
				'http://www.jogossantacasa.pt/',
				'http://www.jn.pt/PaginaInicial/Policia/Interior.aspx?content_id=1902690',
				'http://www.cmjornal.xl.pt/detalhe/noticias/exclusivo-cm/angelico-gnr-investiga-crime-de-homicidio',
				'http://www.agencia.ecclesia.pt/cgi-bin/noticia.pl?id=31713',
				'http://www.jn.pt/PaginaInicial/Mundo/Interior.aspx?content_id=1770243',
		);
		
		error_reporting( E_ALL );
		if ($urls === null) {
			$urls = &$_urls;
		}
		
		foreach ( $urls as $url ) {
			$instance->load( $url );
			echo "$url\n";
			echo str_repeat( '-', 200 ) . "\n";
			//var_dump( $instance->get( 'favicon' ) );
			var_dump( $instance->get( array('title', 'excerpt') ) );
			//var_dump($instance->get());
			echo "\n\n";
			//$instance->reset();
		}
	} // runTests }}}
}

/**************************************
 * 
 * Text class
 * 
 **************************************/
class HTML_PageExcerpt_Text extends HTML_PageExcerpt_Object
{
	/**
	 * @var string
	 * @access public
	 */
	public $content;


	/**
	 * Enter description here ...
	 * 
	 * @param string $str
	 * @param [ bool $sanitize ]
	 */
	public function __construct( $str, $sanitize = true )
	{
		$this->content = $sanitize ? $this->sanitize( $str ) : $str;
	} // __construct }}}


	/**
	 * Enter description here ...
	 * 
	 * @param int $length
	 * @param [ string $terminator ]
	 */
	public function truncate( $length, $terminator = ' ...' )
	{
		if (empty( $this->content ) || strlen( $this->content ) <= $this->content) {
			return $this->content;
		}
		
		$this->content = HTML_PageExcerpt_Utils::substrw( $this->content, $length, $terminator );
		return $this->content;
	} // _truncate }}}


	/**
	 * Enter description here ...
	 *
	 * @param array $criteria : Possible criterias: min_length, max_length
	 * @return bool
	 * @throws HTML_PageExcerpt_InvalidArgumentException
	 */
	public function matches( $criteria )
	{
		if (! is_array( $criteria )) {
			throw new HTML_PageExcerpt_InvalidArgumentException( 'Wrong argument type, criteria must be an array' );
		}
		
		extract( $criteria );
		
		return 	(empty( $min_length ) || strlen( $this->content ) >= $min_length) && 
			(empty( $max_length ) || strlen( $this->content ) <= $max_length);
	} // matches }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $str
	 * @return string
	 */
	public function sanitize( $str )
	{
		//HTML_PageExcerpt_Utils::hexDump( $str );

		// add a space if a tag is present right next to a word
		$str = preg_replace( '@([^\s]+)(<[^>]+>)@', '\\1 \\2', $str );

		$str = strip_tags( html_entity_decode( $str, ENT_QUOTES, HTML_PageExcerpt_Settings::get( 'encoding' ) ) );

		// turn utf8 nbsp's into normal spaces
		// @see http://en.wikipedia.org/wiki/Non-breaking_space
		$str = str_replace( "\xc2\xa0", ' ', $str );
		// remove extra whitespace
		$str = trim( preg_replace( '@\s\s+@', ' ', $str ) );

		// remove spaces before commas
		$str = preg_replace( '@\s,@', ',', $str );
		
		return $str;
	} // _sanitize }}}


	public function linkify( $target = '_blank' )
	{
		$target = ! empty( $target ) ? ' target="_blank"' : '';
		$this->content = preg_replace( '@(https?://[^\s]+)@', "<a href=\"\\1\"$target>\\1</a>", $this->content );
	}


	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->content;
	} // __toString }}}


	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty( $this->content );
	} // isEmpty }}}
}

/**************************************
 * 
 * Image class
 * 
 **************************************/
class HTML_PageExcerpt_Image extends HTML_PageExcerpt_Object
{
	/**
	 * @var HTML_PageExcerpt_Url
	 * @access public
	 */
	public $url;
	
	/**
	 * @var int
	 * @access public
	 */
	public $width;
	
	/**
	 * @var int
	 * @access public
	 */
	public $height;
	
	/**
	 * @var string
	 * @access public
	 */
	public $mimetype;
	
	/**
	 * @var string
	 * @access public
	 */
	public $extension;
	
	/**
	 * @var int
	 * @access public
	 */
	public $size;
	
	/**
	 * @var array
	 * @access protected
	 */
	protected $_fields = array( 
			'url', 
			'width', 
			'height', 
			'mimetype', 
			'extension', 
			'size' 
	);
	
	/**
	 * @var bool
	 * @access protected
	 */
	protected $_identified = false;
	
	/**
	 * @var string
	 * @access protected
	 */
	protected $_tmpfilename;


	/**
	 * Enter description here ...
	 * 
	 * @param string $url
	 * @param [ bool $identify ]
	 */
	public function __construct( $url, $identify = false )
	{
		parent::__construct();
		
		$this->url = new HTML_PageExcerpt_Url( $url, $sanitize = true, $fetch = false );
		if ($identify) {
			$this->identify();
		}
	} // __construct }}}


	/**
	 * Enter description here ...
	 */
	public function __destruct()
	{
		parent::__destruct();
		
		if ($this->_tmpfilename) {
			@unlink( $this->_tmpfilename );
		}
	} // __destruct }}}


	/**
	 * Enter description here ...
	 * 
	 * @throws HTML_PageExcerpt_InvalidImageFileException
	 */
	public function identify()
	{
		if ($this->_identified) {
			return;
		}
		
		$this->url->fetch();
		$this->_tmpfilename = HTML_PageExcerpt_Utils::tempFilename( 'imgpe_' );
		$this->url->save( $this->_tmpfilename );
		// free some memory
		unset( $this->url->content );
		
		$this->mimetype = HTML_PageExcerpt_Utils::fileMimetype( $this->_tmpfilename );
		if (strpos( $this->mimetype, 'image/' ) !== 0) {
			throw new HTML_PageExcerpt_InvalidImageFileException( 'File is not a valid image' );
		}
		
		$this->extension = HTML_PageExcerpt_Utils::sysMimetypeToExtension( $this->mimetype );
		$this->size = filesize( $this->_tmpfilename );
		list( $this->width, $this->height ) = $this->_getImageSize( $this->_tmpfilename );
		
		$this->_identified = true;
	} // identify }}}


	/**
	 * Enter description here ...
	 *
	 * @param $criteria
	 * 		Possible criterias:
	 *			mimetypes_include, mimetypes_exclude
	 *			extensions_include, extensions_exclude
	 *			min_width, max_width, min_height, max_height, min_size, max_size
	 * @return bool
	 * @throws HTML_PageExcerpt_InvalidArgumentException
	 * @throws HTML_PageExcerpt_FatalException
	 */
	public function matches( $criteria )
	{
		if (! is_array( $criteria )) {
			throw new HTML_PageExcerpt_InvalidArgumentException( 'Wrong argument type, criteria must be an array' );
		}
		
		if (! $this->_identified) {
			$this->identify();
		}
		
		extract( $criteria );
		
		if ((! empty( $mimetypes_exclude ) || ! empty( $mimetypes_include )) && (! empty( $extensions_exclude ) || ! empty( $extensions_include ))) {
			throw new HTML_PageExcerpt_FatalException( 'Can\'t do mimetypes include/exclude together with extensions include/exclude, please use only one of both' );
		}
		
		if (! empty( $extensions_include )) {
			if (empty( $this->extension )) {
				throw new HTML_PageExcerpt_FatalException( 'Extension is empty can\'t do matching' );
			}
			
			$mimetypes_include = array();
			foreach ( $extensions_include as $ext ) {
				$mime = HTML_PageExcerpt_Utils::sysExtensionToMimetype( $ext );
				if (! empty( $mime )) {
					$mimetypes_include[] = $mime;
				}
			}
		}
		
		if (! empty( $extensions_exclude )) {
			if (empty( $this->extension )) {
				throw new HTML_PageExcerpt_FatalException( 'Extension is empty can\'t do matching' );
			}
			
			$mimetypes_exclude = array();
			foreach ( $extensions_exclude as $ext ) {
				$mime = HTML_PageExcerpt_Utils::sysExtensionToMimetype( $ext );
				if (! empty( $mime )) {
					$mimetypes_exclude[] = $mime;
				}
			}
		}
		
		if (! empty( $mimetypes_include )) {
			if (empty( $this->mimetype )) {
				throw new HTML_PageExcerpt_FatalException( 'Mimetype is empty can\'t do matching' );
			}
			if (! is_array( $mimetypes_include )) {
				$mimetypes_include = (array) $mimetypes_include;
			}
			
			if (! in_array( $this->mimetype, $mimetypes_include )) {
				return false;
			}
		}
		
		if (! empty( $mimetypes_exclude )) {
			if (empty( $this->mimetype )) {
				throw new HTML_PageExcerpt_FatalException( 'Mimetype is empty can\'t do matching' );
			}
			if (! is_array( $mimetypes_exclude )) {
				$mimetypes_exclude = (array) $mimetypes_exclude;
			}
			
			if (in_array( $this->mimetype, $mimetypes_exclude )) {
				return false;
			}
		}
		
		if (empty( $this->width ) && (! empty( $min_width ) || ! empty( $max_width ))) {
			throw new HTML_PageExcerpt_FatalException( 'Width is empty can\'t do matching' );
		}
		
		if (empty( $this->height ) && (! empty( $min_height ) || ! empty( $max_height ))) {
			throw new HTML_PageExcerpt_FatalException( 'Height is empty can\'t do matching' );
		}
		
		return 	(empty( $min_width ) || $this->width >= $min_width) && 
				(empty( $max_width ) || $this->width <= $max_width) && 
				(empty( $min_height ) || $this->height >= $min_height) && 
				(empty( $max_height ) || $this->height <= $max_height) && 
				(empty( $min_size ) || $this->size >= $min_size) && 
				(empty( $max_size ) || $this->size <= $max_size);
	} // matches }}}


	/**
	 * Enter description here ...
	 * 
	 * @param [ array|string $fields ]
	 * @return mixed
	 */
	public function info( $fields = null )
	{
		if (! $this->_identified) {
			$this->identify();
		}
		
		if ($fields !== null && ! is_array( $fields )) {
			$fields = (array) $field;
		}
		
		$getAll = ($fields === null || $fields === array( 
				'*' 
		));
		
		$data = array();
		foreach ( $this->_fields as $f ) {
			if ($getAll || in_array( $f, $fields )) {
				// typecast to string url class
				$data[$f] = ($f === 'url') ? (string) $this->{$f} : $this->{$f};
			}
		}
		
		return $data;
	} // info }}}


	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isIco()
	{
		// use mimetype here, since extension is not fully supported
		return ($this->mimetype === 'image/x-icon');
	} // isIco }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $path
	 * @param [ int $perms ]
	 */
	public function save( $path, $perms = 0666 )
	{
		return $this->url->save( $path, $perms );
	} // save }}}


	/**
	 * Enter description here ...
	 * 
	 * @return string
	 * @throws HTML_PageExcerpt_FileReadWriteException
	 */
	public function content()
	{
		if (! $this->_identified) {
			$this->identify();
		}
		
		if (false === ($content = @file_get_contents( $this->_tmpfilename ))) {
			throw new HTML_PageExcerpt_FileReadWriteException( "Could not read contents of file '{$this->_tmpfilename}'" );
		}
		return $content;
	} // content }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $file
	 * @return array
	 */
	protected function _getImageSize( $file )
	{
		if ($this->isIco() && ! defined( 'IMAGETYPE_ICO' )) {
			// if PHP version doesn't support ico for getimagesize, use the fallback class
			$ico = new ico();
			$ico->LoadFile( $file );
			$info = $ico->GetIconInfo( 0 );
			$width = $info['Width'];
			$height = $info['Height'];
		} else {
			list ( $width, $height ) = @getimagesize( $file );
		}
		
		return array( 
				$width, 
				$height 
		);
	} // _getImageSize }}}


	/**
	 * This is required because array_unique typecasts to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->url;
	} // __toString }}}
}

/**************************************
 * 
 * Url class
 * 
 **************************************/
class HTML_PageExcerpt_Url extends HTML_PageExcerpt_Object
{
	/**
	 * @var string
	 * @access public
	 */
	public $url;
	
	/**
	 * @var string
	 * @access public
	 */
	public $content;
	
	/**
	 * @var string
	 * @access public
	 */
	public $contentType;
	
	/**
	 * @var string
	 * @access public
	 */
	public $encoding;
	
	/**
	 * @var bool
	 * @access protected
	 */
	protected $_fetched = false;
	
	/**
	 * @var array
	 * @access protected
	 */
	protected $_fetchDefaultOptions = array( 
			'timeout' => 20, 
			'user_agent' => 'PHP', 
			'follow_location' => true, 
			'max_redirs' => 2, 
			// scheme://host:port
			'proxy' => '', 
			'fake_referer' => true 
	);
	
	/**
	 * @var array
	 * @access protected
	 */
	protected $_recognizedSchemes = array( 
			'http', 
			'https' 
	);


	/**
	 * Enter description here ...
	 * 
	 * @param [ string $url ]
	 * @param [ bool $sanitize ]
	 * @param [ bool $fetch ]
	 * @throws HTML_PageExcerpt_CommunicationException
	 */
	public function __construct( $url = null, $sanitize = true, $fetch = false )
	{
		if ($url !== null) {
			$url = $this->set( $url, $sanitize );
			if ($fetch) {
				if (false === ($content = $this->fetch())) {
					throw new HTML_PageExcerpt_CommunicationException( "Error fetching url '$url'" );
				}
				$this->content = $content;
			}
			$this->url = $url;
		}
	} // __construct }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $url
	 * @param [ bool $sanitize ]
	 * @return string
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
	 * @param string $base
	 * @return string
	 * @throws HTML_PageExcerpt_InvalidArgumentException
	 */
	public function absolutize( $base )
	{
		if (! $this->isAbsolute( $base )) {
			throw new HTML_PageExcerpt_InvalidArgumentException( "Supplied source '$source' is not a valid absolute url" );
		}
		
		$url = $this->url;
		if (empty( $base ) || empty( $url ) || $this->isAbsolute( $url )) {
			return $url;
		}
		
		/* queries and anchors */
		if ($url[0] == '#' || $url[0] == '?') {
			$url = $base . $url;
			$this->url = $url;
			return $url;
		}
		
		extract( array_merge( array( 
				'scheme' => '', 
				'host' => '', 
				'port' => '', 
				'user' => '', 
				'pass' => '', 
				'path' => '', 
				'query' => '', 
				'fragment' => '' 
		), parse_url( $base ) ) );
		
		/* remove non-directory element from path */
		$path = preg_replace( '@/[^/]*$@', '', $path );
		
		/* destroy path if relative url points to root */
		if ($url[0] == '/') {
			$path = '';
		}
		
		/* dirty absolute URL */
		$abs = "$host" . (($port !== '' && $port !== 80) ? ":$port" : '') . "$path/{$url}";
		
		/* replace '//' or '/./' or '/foo/../' with '/' */
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
	 * @param [ string $str ]
	 * @return bool
	 */
	public function isAbsolute( $str = null )
	{
		$str = ($str === null) ? $this->url : $str;
		return (bool) preg_match( '@^(' . implode( '|', $this->_recognizedSchemes ) . ')://.+$@i', $str );
	} // isAbsolute }}}


	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		return $this->isAbsolute();
	} // isValid }}}


	/**
	 * Enter description here ...
	 * 
	 * @return string
	 * @throws HTML_PageExcerpt_InvalidArgumentException
	 * @throws HTML_PageExcerpt_CommunicationException
	 */
	public function fetch()
	{
		if (! $this->isAbsolute()) {
			throw new HTML_PageExcerpt_InvalidArgumentException( "Url '{$this->url}' is not a valid absolute url" );
		}
		
		$options = array();
		foreach ( $this->_fetchDefaultOptions as $opt => $value ) {
			$test = HTML_PageExcerpt_Settings::get( "fetcher_$opt" );
			$options[$opt] = ! empty( $test ) ? $test : $value;
		}
		extract( $options );
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, $follow_location );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, $max_redirs );
		if (! empty( $proxy )) {
			curl_setopt( $ch, CURLOPT_PROXY, $proxy );
		}
		if ($fake_referer) {
			curl_setopt( $ch, CURLOPT_REFERER, $this->url );
		}
		
		$logstr = "fetching url: '{$this->url}', timeout: $timeout, ua: $user_agent, follow_loc: " . ($follow_location ? 'y' : 'n');
		$logstr .= ", max_redirs: $max_redirs, proxy: $proxy, fake_ref: " . ($fake_referer ? 'y' : 'n');
		$this->log( $logstr, 'debug' );
		
		$content = curl_exec( $ch );

		$httpStatusCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$contentType = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );

		$encoding = null;
		$matches = array();
		if (preg_match( '@^.+;\s*charset=(.*)$@', $contentType, $matches )) {
			$encoding = $matches[1];
		}
		curl_close( $ch );
		
		if ($content === false || $httpStatusCode !== 200) {
			throw new HTML_PageExcerpt_CommunicationException( "Error fetching content from url '{$this->url}'" );
		}
		
		$this->_fetched = true;
		$this->content = $content;
		$this->contentType = $contentType;
		$this->encoding = $encoding;
		return $content;
	} // fetch }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $path
	 * @param [ int $perms ]
	 * @throws HTML_PageExcerpt_FileReadWriteException
	 */
	public function save( $path, $perms = 0666 )
	{
		if (! $this->_fetched) {
			$this->fetch();
		}
		
		if (false === @file_put_contents( $path, $this->content(), $perms )) {
			throw new HTML_PageExcerpt_FileReadWriteException( "Error saving contents to file '$path'" );
		}
		$this->log( "Saved file to '$path'", 'debug' );
		return true;
	} // save }}}


	/**
	 * Enter description here ...
	 */
	public function content()
	{
		if (! $this->_fetched) {
			$this->fetch();
		}
		return $this->content;
	} // content }}}


	public function __toString()
	{
		return $this->url;
	} // __toString }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $url
	 * @return $url
	 */
	public function sanitize( $url )
	{
		return trim( $url );
	} // sanitize }}}
}

/**************************************
 * 
 * Object class
 * all classes extend this one
 * 
 **************************************/
class HTML_PageExcerpt_Object
{


	/**
	 * Enter description here ...
	 */
	public function __construct()
	{
	
	} // __construct }}}


	/**
	 * Enter description here ...
	 */
	public function __destruct()
	{
	
	} // __destruct }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $str
	 * @param string $level
	 * @return bool
	 */
	public function log( $str, $level )
	{
		$logfile = HTML_PageExcerpt_Settings::get( 'logfile' );
		if (HTML_PageExcerpt_Settings::get( 'logging' ) && ! empty( $logfile )) {
			return HTML_PageExcerpt_Utils::log( $logfile, $str, $level );
		}
		return false;
	} // log }}}
}

/**************************************
 * 
 * Utilities class
 * 
 **************************************/
class HTML_PageExcerpt_Utils
{
	/**
	 * Enter description here ...
	 * 
	 * @param string $logfile
	 * @param string $str
	 * @param string $level
	 * @param bool $appendNewLine
	 * @throws HTML_PageExcerpt_FatalException
	 * @throws HTML_PageExcerpt_FileReadWriteException
	 */
	public static function log( $logfile, $str, $level, $appendNewline = true )
	{
		if (! in_array( $level, array( 
				'debug', 
				'notice', 
				'warning', 
				'error', 
				'critical' 
		) )) {
			throw new HTML_PageExcerpt_FatalException( "Invalid log level specified '$level'" );
		}
		$str = date( 'M  d H:i:s' ) . ' ' . __CLASS__ . ": $level >> $str" . ($appendNewline ? "\n" : '');
		if (false === @file_put_contents( $logfile, $str, FILE_APPEND )) {
			throw new HTML_PageExcerpt_FileReadWriteException( "Error writing to logfile '$logfile'" );
		}
		return true;
	} // log }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $str
	 * @param int $length
	 * @param [ string $terminator ]
	 * @param [ int $minword ]
	 * @return string
	 */
	public static function substrw( $str, $length, $terminator = '...', $minword = 3 )
	{
		$sub = '';
		$len = 0;
		
		foreach ( explode( ' ', $str ) as $word ) {
			$part = (($sub != '') ? ' ' : '') . $word;
			$sub .= $part;
			$len += strlen( $part );
			
			if (strlen( $word ) > $minword && strlen( $sub ) >= $length) {
				break;
			}
		}
		
		return $sub . (($len < strlen( $str )) ? $terminator : '');
	} // substrw }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $prefix
	 * @param [ bool $touch ]
	 * @return string
	 * @throws HTML_PageExcerpt_FileReadWriteException
	 */
	public static function tempFilename( $prefix, $touch = false )
	{
		$tmpname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . md5( uniqid( microtime( true ), true ) );
		if ($touch) {
			if (false === @touch( $tmpname )) {
				throw new HTML_PageExcerpt_FileReadWriteException( "Unable to touch filename '$tmpname', check permissions" );
			}
		}
		return $tmpname;
	} // tempFilename }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $filename
	 * @return string
	 */
	public static function fileMimetype( $filename )
	{
		$mime = MIME_Type::autoDetect( $filename );
		// fix erroneous returned mimetype for ico files
		if ($mime === 'image/x-ico') {
			$mime = 'image/x-icon';
		}
		return $mime;
	} // fileMimetype }}}


	/**
	 * Enter description here ...
	 * 
	 * @param DOMDocumentNode
	 * @return string
	 */
	public static function DOMinnerHTML( $element )
	{
		$innerHTML = '';
		$children = $element->childNodes;
		foreach ( $children as $child ) {
			$tmp_dom = new DOMDocument();
			$tmp_dom->appendChild( $tmp_dom->importNode( $child, true ) );
			$innerHTML .= trim( $tmp_dom->saveHTML() );
		}
		return $innerHTML;
	} // DOMinnerHTML }}}


	/**
	 * Enter description here ...
	 * 
	 * @return array
	 */
	public static function sysExtensionMimetypes()
	{
		$mimeMap = self::_getMimetypesMap();
		# Returns the system MIME type mapping of extensions to MIME types, as defined in /etc/mime.types.
		$out = array();
		$file = fopen( self::$sysMimetypesMap, 'r' );
		while ( ($line = fgets( $file )) !== false ) {
			$line = trim( preg_replace( '@#.*@', '', $line ) );
			if (! $line) {
				continue;
			}
			$parts = preg_split( '@\s+@', $line );
			if (count( $parts ) == 1) {
				continue;
			}
			$type = array_shift( $parts );
			foreach ( $parts as $part ) {
				$out[$part] = $type;
			}
		}
		fclose( $file );
		return $out;
	} // sysExtensionMimetypes }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $file
	 * @return string
	 */
	public static function sysExtensionToMimetype( $file )
	{
		# Returns the system MIME type (as defined in /etc/mime.types) for the filename specified.
		#
		# $file - the filename to examine
		static $types;
		if (! isset( $types )) {
			$types = self::sysExtensionMimetypes();
		}
		$ext = pathinfo( $file, PATHINFO_EXTENSION );
		if (! $ext) {
			$ext = $file;
		}
		$ext = strtolower( $ext );
		return isset( $types[$ext] ) ? $types[$ext] : null;
	} // sysExtensionToMimetype }}}


	/**
	 * Enter description here ...
	 * 
	 * @return array
	 */
	public static function sysMimetypeExtensions()
	{
		$mimeMap = self::_getMimetypesMap();
		# Returns the system MIME type mapping of MIME types to extensions, as defined in /etc/mime.types (considering the first
		# extension listed to be canonical).
		$out = array();
		$file = fopen( $mimeMap, 'r' );
		while ( ($line = fgets( $file )) !== false ) {
			$line = trim( preg_replace( '@#.*@', '', $line ) );
			if (! $line) {
				continue;
			}
			$parts = preg_split( '@\s+@', $line );
			if (count( $parts ) == 1) {
				continue;
			}
			$type = array_shift( $parts );
			if (! isset( $out[$type] )) {
				$out[$type] = array_shift( $parts );
			}
		}
		fclose( $file );
		return $out;
	} // sysMimetypeExtensions }}}


	/**
	 * Enter description here ...
	 * 
	 * @param string $type
	 * @return string
	 */
	public static function sysMimetypeToExtension( $type )
	{
		# Returns the canonical file extension for the MIME type specified, as defined in /etc/mime.types (considering the first
		# extension listed to be canonical).
		#
		# $type - the MIME type
		static $exts;
		if (! isset( $exts )) {
			$exts = self::sysMimetypeExtensions();
		}
		$extension = isset( $exts[$type] ) ? $exts[$type] : null;
		// prefer jpg over jpeg
		if ($extension === 'jpeg') {
			$extension = 'jpg';
		}
		return $extension;
	} // sysMimetypeToExtension }}}


	/**
	 * Enter description here ...
	 * 
	 * @return array
	 * @throws HTML_PageExcerpt_FatalException
	 */
	protected static function _getMimetypesMap()
	{
		static $checked = false;
		
		$mimetypesMap = HTML_PageExcerpt_Settings::get( 'mimetypes_map_path' );
		if (! $checked) {
			if (! is_file( $mimetypesMap )) {
				throw new HTML_PageExcerpt_FatalException( "System mimetypes map not found '$mimetypesMap'" );
			}
			if (! is_readable( $mimetypesMap )) {
				throw new HTML_PageExcerpt_FatalException( "System mimetypes map not readable '$mimetypesMap'" );
			}
			$checked = true;
		}
		
		return $mimetypesMap;
	} // _getMimetypesMap }}}


	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public static function isWindows()
	{
		// praize to god it returns false :)
		return (strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN');
	} // isWindows }}}

	/**
	 * Enter description here ...
	 * 
	 * @param string $data
	 * @param string $newline
	 */
	public static function hexDump( $data, $newline = "\n" )
	{
		static $from = '';
		static $to = '';

		static $width = 16; # number of bytes per line

		static $pad = '.'; # padding for non-visible characters

		if ($from === '') {
			for ($i=0; $i<=0xFF; $i++) {
				$from .= chr($i);
				$to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
			}
		}

		$hex = str_split( bin2hex( $data ), $width*2 );
		$chars = str_split( strtr( $data, $from, $to ), $width );

		$offset = 0;
		foreach ( $hex as $i => $line ) {
			echo sprintf( '%6X', $offset).' : '.implode( ' ', str_split( $line, 2 ) ) . ' [' . $chars[$i] . ']' . $newline;
			$offset += $width;
		}
	} // hexDump }}}
}

/*
 * Exception classes
 */
class HTML_PageExcerpt_Exception extends Exception {}
class HTML_PageExcerpt_CommunicationException extends HTML_PageExcerpt_Exception {}
class HTML_PageExcerpt_InvalidArgumentException extends HTML_PageExcerpt_Exception {}
class HTML_PageExcerpt_FileReadWriteException extends HTML_PageExcerpt_Exception {}
class HTML_PageExcerpt_InvalidImageFileException extends HTML_PageExcerpt_Exception {}
class HTML_PageExcerpt_FatalException extends HTML_PageExcerpt_Exception {}


/**
 * Class Ico
 * Open ICO files and extract any size/depth to PNG format
 *
 * @author Diogo Resende <me@diogoresende.net>
 * @version 0.1
 *
 * @method public  Ico($path = '')
 * @method public  LoadFile($path)
 * @method private LoadData($data)
 * @method public  TotalIcons()
 * @method public  GetIconInfo($index)
 **/
class Ico
{
	/**
	 * Ico::Ico()
	 * Class constructor
	 *
	 * @param   optional    string   $path   Path to ICO file
	 * @return              void
	 **/
	function Ico( $path = '' )
	{
		if (strlen( $path ) > 0) {
			$this->LoadFile( $path );
		}
	}


	/**
	 * Ico::LoadFile()
	 * Load an ICO file (don't need to call this is if fill the
	 * parameter in the class constructor)
	 *
	 * @param   string   $path   Path to ICO file
	 * @return  boolean          Success
	 **/
	function LoadFile( $path )
	{
		$this->_filename = $path;
		if (($fp = @fopen( $path, 'rb' )) !== false) {
			$data = '';
			while ( ! feof( $fp ) ) {
				$data .= fread( $fp, 4096 );
			}
			fclose( $fp );
			
			return $this->LoadData( $data );
		}
		return false;
	}


	/**
	 * Ico::LoadData()
	 * Load an ICO data. If you prefer to open the file
	 * and return the binary data you can use this function
	 * directly. Otherwise use LoadFile() instead.
	 *
	 * @param   string   $data   Binary data of ICO file
	 * @return  boolean          Success
	 **/
	function LoadData( $data )
	{
		$this->formats = array();
		
		/**
		 * ICO header
		 **/
		$icodata = unpack( "SReserved/SType/SCount", $data );
		$this->ico = $icodata;
		$data = substr( $data, 6 );
		
		/**
		 * Extract each icon header
		 **/
		for ($i = 0; $i < $this->ico['Count']; $i ++) {
			$icodata = unpack( "CWidth/CHeight/CColorCount/CReserved/SPlanes/SBitCount/LSizeInBytes/LFileOffset", $data );
			$icodata['FileOffset'] -= ($this->ico['Count'] * 16) + 6;
			if ($icodata['ColorCount'] == 0)
				$icodata['ColorCount'] = 256;
			$this->formats[] = $icodata;
			
			$data = substr( $data, 16 );
		}
		
		/**
		 * Extract aditional headers for each extracted icon header
		 **/
		for ($i = 0; $i < count( $this->formats ); $i ++) {
			$icodata = unpack( "LSize/LWidth/LHeight/SPlanes/SBitCount/LCompression/LImageSize/LXpixelsPerM/LYpixelsPerM/LColorsUsed/LColorsImportant", substr( $data, $this->formats[$i]['FileOffset'] ) );
			
			$this->formats[$i]['header'] = $icodata;
			$this->formats[$i]['colors'] = array();
			
			$this->formats[$i]['BitCount'] = $this->formats[$i]['header']['BitCount'];
			
			switch ($this->formats[$i]['BitCount']) {
			case 32:
			case 24:
				$length = $this->formats[$i]['header']['Width'] * $this->formats[$i]['header']['Height'] * ($this->formats[$i]['BitCount'] / 8);
				$this->formats[$i]['data'] = substr( $data, $this->formats[$i]['FileOffset'] + $this->formats[$i]['header']['Size'], $length );
				break;
			case 8:
			case 4:
				$icodata = substr( $data, $this->formats[$i]['FileOffset'] + $icodata['Size'], $this->formats[$i]['ColorCount'] * 4 );
				$offset = 0;
				/*for ($j = 0; $j < $this->formats[$i]['ColorCount']; $j ++) {
					$this->formats[$i]['colors'][] = array( 
							'red' => ord( $icodata[$offset] ), 
							'green' => ord( $icodata[$offset + 1] ), 
							'blue' => ord( $icodata[$offset + 2] ), 
							'reserved' => ord( $icodata[$offset + 3] ) 
					);
					$offset += 4;
				}*/
				$length = $this->formats[$i]['header']['Width'] * $this->formats[$i]['header']['Height'] * (1 + $this->formats[$i]['BitCount']) / $this->formats[$i]['BitCount'];
				$this->formats[$i]['data'] = substr( $data, $this->formats[$i]['FileOffset'] + ($this->formats[$i]['ColorCount'] * 4) + $this->formats[$i]['header']['Size'], $length );
				break;
			case 1:
				$icodata = substr( $data, $this->formats[$i]['FileOffset'] + $icodata['Size'], $this->formats[$i]['ColorCount'] * 4 );
				
				/*$this->formats[$i]['colors'][] = array( 
						'blue' => ord( $icodata[0] ), 
						'green' => ord( $icodata[1] ), 
						'red' => ord( $icodata[2] ), 
						'reserved' => ord( $icodata[3] ) 
				);
				$this->formats[$i]['colors'][] = array( 
						'blue' => ord( $icodata[4] ), 
						'green' => ord( $icodata[5] ), 
						'red' => ord( $icodata[6] ), 
						'reserved' => ord( $icodata[7] ) 
				);*/
				
				$length = $this->formats[$i]['header']['Width'] * $this->formats[$i]['header']['Height'] / 8;
				$this->formats[$i]['data'] = substr( $data, $this->formats[$i]['FileOffset'] + $this->formats[$i]['header']['Size'] + 8, $length );
				break;
			}
			$this->formats[$i]['data_length'] = strlen( $this->formats[$i]['data'] );
		}
		
		return true;
	}


	/**
	 * Ico::TotalIcons()
	 * Return the total icons extracted at the moment
	 *
	 * @return  integer   Total icons
	 **/
	function TotalIcons()
	{
		return count( $this->formats );
	}


	/**
	 * Ico::GetIconInfo()
	 * Return the icon header corresponding to that index
	 *
	 * @param   integer   $index    Icon index
	 * @return  resource            Icon header
	 **/
	function GetIconInfo( $index )
	{
		if (isset( $this->formats[$index] )) {
			return $this->formats[$index];
		}
		return false;
	}
}

?>
