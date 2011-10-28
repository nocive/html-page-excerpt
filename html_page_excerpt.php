<?php

/**
 * HTML Page Excerpt class
 * This class attempts to retrieve an excerpt for the requested url, as seen on facebook "share" feature
 * It currently supports: title, text excerpt, thumbnails and favicon
 *
 *
 * @author Jose' Pedro Saraiva <nocive at gmail.com>
 * @package HTML_PageExcerpt
 */

define( 'HTML_PAGEEXCERPT_PATH', realpath( dirname( __FILE__ ) ) );
define( 'HTML_PAGEEXCERPT_LOGPATH', HTML_PAGEEXCERPT_PATH );
define( 'HTML_PAGEEXCERPT_LOGFILE', HTML_PAGEEXCERPT_LOGPATH . DIRECTORY_SEPARATOR . 'html_pageexcerpt.log' );

  

/**
 * Settings class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	HTML_PageExcerpt::Settings
 */
class HTML_PageExcerpt_Settings
{
	/**
	 * @var		array
	 * @access	public
	 */
	public static $settings = array( 
			'logging' => false, 
			'logfile' => HTML_PAGEEXCERPT_LOGFILE, 
			'encoding' => 'UTF-8', 
			
			'fetcher_proxy' => '', 
			//'fetcher_proxy' => 'http://10.135.32.7:3128',
			'fetcher_timeout' => 60, 
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
					'article section',
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
			'thumbnails_found_stop_count' => 0, 
			'thumbnails_min_width' => 100, 
			'thumbnails_min_height' => 100, 
			'thumbnails_max_width' => 0, 
			'thumbnails_max_height' => 0, 
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
			'favicon_min_width' => 0, 
			'favicon_min_height' => 0, 
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
	 * Enter description here ...
	 * 
	 * @param	string $setting				optional
	 * @param	mixed $value
	 * @throws	HTML_PageExcerpt_InvalidArgumentException
	 * @return	void
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
	 * @param	string $setting		optional
	 * @return	mixed
	 */
	public static function get( $setting = null )
	{
		if ($setting === null) {
			return self::$settings;
		}
		return isset( self::$settings[$setting] ) ? self::$settings[$setting] : null;
	} // get }}}
}


/**
 * Main class
 *
 * @package	HTML_PageExcerpt
 */
class HTML_PageExcerpt extends HTML_PageExcerpt_Object
{
	/**
	 * @var		array
	 * @access	public
	 */
	public static $extensions = array( 
			'curl', 
			'DOM', 
			'iconv', 
			'fileinfo',
			//'tidy' 
	);
	
	/**
	 * @var		array
	 * @access	public
	 */
	public static $dependencies = array( 
			'Mimex/mimex.php'
	);

	/**
	 * @var		HTML_PageExcerpt_Url
	 * @access	public
	 */
	public $url;
	
	/**
	 * @var		string
	 * @access	public
	 */
	public $html;
	
	/**
	 * @var		HTML_PageExcerpt_Text
	 * @access	public
	 */
	public $title;
	
	/**
	 * @var		HTML_PageExcerpt_Text
	 * @access	public
	 */
	public $excerpt;
	
	/**
	 * @var		HTML_PageExcerpt_Image
	 * @access	public
	 */
	public $favicon;
	
	/**
	 * @var		array
	 * @access	public
	 */
	public $thumbnails;
	
	/**
	 * @var		DOMDocument
	 * @access	protected
	 */
	protected $_dom;
	
	/**
	 * @var		DOMXPath
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
			'title',
			'excerpt', 
			'thumbnails', 
			'favicon' 
	);

	const PHP_MIN_VERSION = '5.3.0';


	/**
	 * Enter description here ...
	 * 
	 * @param	string $source		optional
	 * @param	array $settings		optional
	 * @return	void
	 */
	public function __construct( $source = null, $settings = null )
	{
		$this->checkRequirements();

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
	 * @param	string $source					optional
	 * @throws	HTML_PageExcerpt_FileReadWriteException
	 * @return	void
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
	 * @param	string $html
	 * @param	string $url				optional
	 * @throws	HTML_PageExcerpt_FatalException
	 * @return	void
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
	 * @param	string $setting		optional
	 * @param	mixed $value
	 * @return	mixed
	 */
	public function config( $setting = null, $value )
	{
		return HTML_PageExcerpt_Settings::set( $setting, $value );
	} // config }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	mixed $fields				string, array or null
	 * @throws	HTML_PageExcerpt_FatalException
	 * @return	mixed
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
	 *
	 * @return	void
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
	 * @throws	HTML_PageExcerpt_FatalException
	 * @return	void
	 */
	public function checkRequirements()
	{
		static $checked = false;

		if (! $checked) {
			if (version_compare( PHP_VERSION, self::PHP_MIN_VERSION ) < 0) {
				throw new HTML_PageExcerpt_FatalException( 'This class requires PHP version >= ' . self::PHP_MIN_VERSION );
			}
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
		}
	} // checkRequirements }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $html
	 * @return	void
	 */
	protected function _loadDocument( $html )
	{
		$dom = new DOMDocument();
		$dom->preserveWhitespace = false;
		@$dom->loadHTML( $html );
	 
		$dom->encoding = $this->_getEncoding( $dom );
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
	 * @param	DOMDocument $dom
	 * @return	string
	 */
	protected function _getEncoding( &$dom )
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
			unset( $xpath );
			$matches = array();
			if ($elements->length === 1 && preg_match( '@^.+;\s*charset=(.*)$@', $elements->item( 0 )->nodeValue, $matches )) {
				return $matches[1];
			}
		}
		
		// assume default encoding if no encoding found
		return empty( $dom->encoding ) ? HTML_PageExcerpt_Settings::get( 'encoding' ) : $dom->encoding;
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

		// remove script, style and iframe tags
		$html = preg_replace( '@<\s*\b(script|style|iframe)\b[^>]*>(.*?)<\s*\/\s*(script|style|iframe)\s*>@is', '', $html );

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
		$html = $tidy->repairString( $html, array( 'wrap' => 0 ), str_replace( '-', '', $toEncoding ) );
		*/

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
	 * @return	HTML_PageExcerpt_Text
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

		if (HTML_PageExcerpt_Settings::get( 'title_truncate' ) && $title instanceof HTML_PageExcerpt_Text) {
			$title->truncate( HTML_PageExcerpt_Settings::get( 'title_truncate_length' ), HTML_PageExcerpt_Settings::get( 'title_truncate_terminator' ) );
		}
		$this->title = $title;
		return $title;
	} // _findTitle }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	HTML_PageExcerpt_Text
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
			case 'article section':
				// new html5 method
				$elements = $this->_xpath->query( '/html/body//article/section' );
				foreach ( $elements as $elem ) {
					$candidate = new HTML_PageExcerpt_Text( $elem->nodeValue );
					if ($candidate->matches( $this->_getFilterOpts( 'excerpt' ) )) {
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
	 * @return	array
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
			
			// all Url class instances will be converted to string after calling array_unique
			$thumbs = array_unique( $thumbs );

			// we should validate after array_unique, otherwise we could be wasting a lot of resources on repeated url's
			foreach ( $thumbs as $t ) {
				try {
					$t->identify();
					if ($t->matches( $this->_getFilterOpts( 'thumbnails' ) )) {
						$thumbnails[] = $t;
						if (count( $thumbnails ) >= HTML_PageExcerpt_Settings::get( 'thumbnails_found_stop_count' )) {
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
	 * @return	HTML_PageExcerpt_Image
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
	 * @param	string $type
	 * @param	string $url
	 * @return	bool
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
	 * @param	string $type
	 * @return	array
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
}


/**
 * Text class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	HTML_PageExcerpt::Text
 */
class HTML_PageExcerpt_Text extends HTML_PageExcerpt_Object
{
	/**
	 * @var		string
	 * @access	public
	 */
	public $content;


	/**
	 * Enter description here ...
	 * 
	 * @param	string $str
	 * @param	bool $sanitize	optional
	 */
	public function __construct( $str, $sanitize = true )
	{
		$this->content = $sanitize ? $this->sanitize( $str ) : $str;
	} // __construct }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	int $length
	 * @param	string $terminator	optional
	 * @return	string
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
	 * @param	array $criteria					possible criterias: min_length, max_length
	 * @throws	HTML_PageExcerpt_InvalidArgumentException
	 * @return	bool
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
	 * @param	string $str
	 * @return	string
	 */
	public function sanitize( $str )
	{
		//HTML_PageExcerpt_Utils::hexDump( $str );

		// add a space if a tag is present right next to a word
		$str = preg_replace( '@([^\s]+)(<[^>]+>)@', '\\1 \\2', $str );

		$str = strip_tags( html_entity_decode( $str, ENT_QUOTES, HTML_PageExcerpt_Settings::get( 'encoding' ) ) );

		// turn utf8 nbsp's into normal spaces
		// @see http://en.wikipedia.org/wiki/Non-breaking_space
		//$str = str_replace( "\xc2\xa0", ' ', $str );
		// remove extra whitespace
		//$str = trim( preg_replace( '@\s\s+@', ' ', $str ) );

		// replace all known unicode whitespaces with space
		$str = preg_replace( '@[\pZ\pC]+@mu', ' ', $str );
		// remove spaces before commas
		$str = preg_replace( '@\s,@', ',', $str );
		
		return trim( $str );
	} // _sanitize }}}


	/**
	 * Enter description here ...
	 *
	 * @param	string $target
	 * @return	string
	 */
	public function linkify( $target = '_blank' )
	{
		$target = ! empty( $target ) ? ' target="_blank"' : '';
		$this->content = preg_replace( '@(https?://[^\s]+)@', "<a href=\"\\1\"$target>\\1</a>", $this->content );
	} // linkify }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	string
	 */
	public function __toString()
	{
		return $this->content;
	} // __toString }}}


	/**
	 * Enter description here ...
	 * 
	 * @return	bool
	 */
	public function isEmpty()
	{
		return empty( $this->content );
	} // isEmpty }}}
}


/**
 * Image class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	HTML_PageExcerpt::Image
 */
class HTML_PageExcerpt_Image extends HTML_PageExcerpt_Object
{
	/**
	 * @var		HTML_PageExcerpt_Url
	 * @access	public
	 */
	public $url;
	
	/**
	 * @var		int
	 * @access	public
	 */
	public $width;
	
	/**
	 * @var		int
	 * @access	public
	 */
	public $height;
	
	/**
	 * @var		string
	 * @access	public
	 */
	public $mimetype;
	
	/**
	 * @var		string
	 * @access	public
	 */
	public $extension;
	
	/**
	 * @var		int
	 * @access	public
	 */
	public $size;
	
	/**
	 * @var		array
	 * @access	protected
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
	 * @var		bool
	 * @access	protected
	 */
	protected $_identified = false;
	
	/**
	 * @var		string
	 * @access	protected
	 */
	protected $_tmpfilename;


	/**
	 * Enter description here ...
	 * 
	 * @param	string $url
	 * @param	bool $identify	optional
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
	 * @throws	HTML_PageExcerpt_InvalidImageFileException
	 * @throws	HTML_PageExcerpt_FatalException
	 * @return	void
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
		
		$this->mimetype = HTML_PageExcerpt_Utils::fileDetectMimetype( $this->_tmpfilename );
		if (strpos( $this->mimetype, 'image/' ) !== 0) {
			throw new HTML_PageExcerpt_InvalidImageFileException( 'File is not a valid image' );
		}
		
		$this->size = filesize( $this->_tmpfilename );
		if (false === ($imginfo = @getimagesize( $this->_tmpfilename ))) {
			throw new HTML_PageExcerpt_FatalException( "Error calling getimagesize() on image '{$this->_tmpfilename}'" );
		}
		$this->width = $imginfo[0];
		$this->height = $imginfo[1];
		// use image_type_to_extension because it's cheaper
		$this->extension = image_type_to_extension( $imginfo[2], false );
		
		$this->_identified = true;
	} // identify }}}


	/**
	 * Enter description here ...
	 *
	 * @param	$criteria	Possible criterias: mimetypes_include, mimetypes_exclude, extensions_include, extensions_exclude
	 *				min_width, max_width, min_height, max_height, min_size, max_size
	 * @throws	HTML_PageExcerpt_InvalidArgumentException
	 * @throws	HTML_PageExcerpt_FatalException
	 * @return	bool
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
				$mime = HTML_PageExcerpt_Utils::extensionToMimetype( $ext );
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
				$mime = HTML_PageExcerpt_Utils::extensionToMimetype( $ext );
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
	 * @param	mixed $fields	array, string or null for all fields
	 * @return	mixed
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
	 * @param	string $path
	 * @param	int $perms
	 * @return	void
	 */
	public function save( $path, $perms = 0666 )
	{
		return $this->url->save( $path, $perms );
	} // save }}}


	/**
	 * Enter description here ...
	 * 
	 * @throws	HTML_PageExcerpt_FileReadWriteException
	 * @return	string
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
	 * This is required because array_unique typecasts to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->url;
	} // __toString }}}
}


/**
 * Url class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	HTML_PageExcerpt::Url
 */
class HTML_PageExcerpt_Url extends HTML_PageExcerpt_Object
{
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
	 * @param	string $url					optional
	 * @param	bool $sanitize					optional
	 * @param	bool $fetch					optional
	 */
	public function __construct( $url = null, $sanitize = true, $fetch = false )
	{
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
	 * @throws	HTML_PageExcerpt_InvalidArgumentException
	 * @return	string
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
	 * @throws	HTML_PageExcerpt_InvalidArgumentException
	 * @throws	HTML_PageExcerpt_CommunicationException
	 * @return	string
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
		
		$this->_curl = curl_init();
		curl_setopt( $this->_curl, CURLOPT_URL, $this->url );
		curl_setopt( $this->_curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->_curl, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $this->_curl, CURLOPT_USERAGENT, $user_agent );
		curl_setopt( $this->_curl, CURLOPT_FOLLOWLOCATION, $follow_location );
		curl_setopt( $this->_curl, CURLOPT_MAXREDIRS, $max_redirs );
		curl_setopt( $this->_curl, CURLOPT_SSL_VERIFYPEER, false );
		if (! empty( $proxy )) {
			curl_setopt( $this->_curl, CURLOPT_PROXY, $proxy );
		}
		if ($fake_referer) {
			curl_setopt( $this->_curl, CURLOPT_REFERER, $this->url );
		}
		
		$logstr = "fetching url: '{$this->url}', timeout: $timeout, ua: $user_agent, follow_loc: " . ($follow_location ? 'y' : 'n');
		$logstr .= ", max_redirs: $max_redirs, proxy: $proxy, fake_ref: " . ($fake_referer ? 'y' : 'n');
		$this->log( $logstr, 'debug' );
		
		$content = curl_exec( $this->_curl );

		$httpStatusCode = curl_getinfo( $this->_curl, CURLINFO_HTTP_CODE );
		$contentType = curl_getinfo( $this->_curl, CURLINFO_CONTENT_TYPE );

		$encoding = null;
		$matches = array();
		if (preg_match( '@^.+;\s*charset=(.*)$@', $contentType, $matches )) {
			$encoding = $matches[1];
		}
		
		if ($content === false || $httpStatusCode !== 200) {
			throw new HTML_PageExcerpt_CommunicationException( "Error fetching content from url '{$this->url}'" . (curl_errno( $this->_curl ) ? ', curl error: ' . curl_error( $this->_curl ) : '' ) );
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
	 * @param	int $perms					optional
	 * @throws	HTML_PageExcerpt_FileReadWriteException
	 * @return	bool
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


/**
 * Object class, all classes extends this one
 *
 * @package	HTML_PageExcerpt
 * @subpackage	HTML_PageExcerpt::Object
 */
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
	 * @param	string $str
	 * @param	string $level
	 * @return	bool
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


/**
 * Utilities class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	HTML_PageExcerpt::Utils
 */
class HTML_PageExcerpt_Utils
{
	/**
	 * Enter description here ...
	 * 
	 * @param	string $logfile
	 * @param	string $str
	 * @param	string $level
	 * @param	bool $appendNewLine
	 * @throws	HTML_PageExcerpt_FatalException
	 * @throws	HTML_PageExcerpt_FileReadWriteException
	 * @return	void
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
	 * @param	string $str
	 * @param	int $length
	 * @param	string $terminator	optional
	 * @param	int $minword		optional
	 * @return	string
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
	 * @param	string $prefix
	 * @param	bool $touch
	 * @throws	HTML_PageExcerpt_FileReadWriteException
	 * @return	string
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
	 * @param	string $mimetype
	 * @return	string
	 */
	public static function mimetypeToExtension( $mimetype )
	{
		return Mimex::mimetypeToExtension( $mimetype );
	} // mimetypeToExtension }}}


	/**
	 * Enter description here ...
	 *
	 * @param	string $extension
	 * @return	string
	 */
	public static function extensionToMimetype( $extension )
	{
		return Mimex::extensionToMimetype( $extension );
	} // extensionToMimetype }}}


	/**
	 * Enter description here ...
	 *
	 * @param	string $filename
	 * @return	string
	 */
	public static function fileDetectMimetype( $filename )
	{
		return Mimex::mimetype( $filename, true );
	} // fileDetectMimetype }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	DOMNode
	 * @throws	HTML_PageExcerpt_InvalidArgumentException
	 * @return	string
	 */
	public static function DOMinnerHTML( $element )
	{
		if (! $element instanceof DOMNode) {
			throw new HTML_PageExcerpt_InvalidArgumentException( 'Supplied element is not a DOMNode instance' );
		}

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
	 * @param string $data
	 * @param string $newline
	 */
	public static function hexDump( $data, $newline = "\n" )
	{
		static $from = '';
		static $to = '';
		static $width = 16; // number of bytes per line
		static $pad = '.'; // padding for non-visible characters

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

?>
