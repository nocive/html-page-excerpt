<?php

/**
 * Config class
 *
 * @package	HTMLPageExcerpt
 * @subpackage	Config
 */
namespace HTMLPageExcerpt;

class Config
{
	const LOG = 'log';
	const LOGFILE = 'logfile';
	const ENCODING = 'encoding';

	const FETCHER_PROXY = 'fetcher_proxy';
	const FETCHER_TIMEOUT = 'fetcher_timeout';
	const FETCHER_FOLLOW_LOCATION = 'fetcher_follow_location';
	const FETCHER_MAX_REDIRS = 'fetcher_max_redirs';
	const FETCHER_USER_AGENT = 'fetcher_user_agent';
	const FETCHER_FAKE_REFERER = 'fetcher_fake_referer';

	const TITLE_SEARCH_TAGS = 'title_search_tags';
	const TITLE_SEO_TAGS_IGNORE_FILTERS = 'title_seo_tags_ignore_filters';
	const TITLE_MIN_LENGTH = 'title_min_length';
	const TITLE_MAX_LENGTH = 'title_max_length';
	const TITLE_TRUNCATE = 'title_truncate';
	const TITLE_TRUNCATE_LENGTH = 'title_truncate_length';
	const TITLE_TRUNCATE_TERMINATOR = 'title_truncate_terminator';

	const EXCERPT_SEARCH_TAGS = 'excerpt_search_tags';
	const EXCERPT_SEO_TAGS_IGNORE_FILTERS = 'excerpt_seo_tags_ignore_filters';
	const EXCERPT_MIN_LENGTH = 'excerpt_min_length';
	const EXCERPT_MAX_LENGTH = 'excerpt_max_length';
	const EXCERPT_TRUNCATE = 'excerpt_truncate';
	const EXCERPT_TRUNCATE_LENGTH = 'excerpt_truncate_length';
	const EXCERPT_TRUNCATE_TERMINATOR = 'excerpt_truncate_terminator';
	const EXCERPT_LINKIFY = 'excerpt_linkify';

	const THUMBS_SEO_TAGS_IGNORE_FILTERS = 'thumbs_seo_tags_ignore_filters';
	const THUMBS_FOUND_STOP_COUNT = 'thumbs_found_stop_count';
	const THUMBS_MIN_WIDTH = 'thumbs_min_width';
	const THUMBS_MIN_HEIGHT = 'thumbs_min_height';
	const THUMBS_MAX_WIDTH = 'thumbs_max_width';
	const THUMBS_MAX_HEIGHT = 'thumbs_max_height';
	const THUMBS_MAX_TRIES = 'thumbs_max_tries';
	const THUMBS_MIN_SIZE = 'thumbs_min_size';
	const THUMBS_MAX_SIZE = 'thumbs_max_size';
	const THUMBS_MIMETYPES_INCLUDE = 'thumbs_mimetypes_include';
	const THUMBS_MIMETYPES_EXCLUDE = 'thumbs_mimetypes_exclude';
	const THUMBS_EXTENSIONS_INCLUDE = 'thumbs_extensions_include';
	const THUMBS_EXTENSIONS_EXCLUDE = 'thumbs_extensions_exclude';
	const THUMBS_URL_BLACKLIST = 'thumbs_url_blacklist';

	const FAVICON_MIN_WIDTH = 'favicon_min_width';
	const FAVICON_MIN_HEIGHT = 'favicon_min_height';
	const FAVICON_MAX_WIDTH = 'favicon_max_width';
	const FAVICON_MAX_HEIGHT = 'favicon_max_height';
	const FAVICON_MAX_TRIES = 'favicon_max_tries';
	const FAVICON_MIN_SIZE = 'favicon_min_size';
	const FAVICON_MAX_SIZE = 'favicon_max_size';
	const FAVICON_MIMETYPES_INCLUDE = 'favicon_mimetypes_include';
	const FAVICON_MIMETYPES_EXCLUDE = 'favicon_mimetypes_exclude';
	const FAVICON_EXTENSIONS_INCLUDE = 'favicon_extensions_include';
	const FAVICON_EXTENSIONS_EXCLUDE = 'favicon_extensions_exclude';

	/**
	 * @var		array
	 * @access	protected
	 */
	protected static $_defaults = array( 
		self::LOG => false, 
		self::LOGFILE => '',
		self::ENCODING => 'UTF-8', 

		self::FETCHER_PROXY => '', 
		self::FETCHER_TIMEOUT => 20, 
		self::FETCHER_FOLLOW_LOCATION => true, 
		self::FETCHER_MAX_REDIRS => 2, 
		self::FETCHER_USER_AGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.57 Safari/537.1', 
		self::FETCHER_FAKE_REFERER => true, 

		self::TITLE_SEARCH_TAGS => array(
			'meta og:title',
			'title',
			'h1'
		),
		self::TITLE_SEO_TAGS_IGNORE_FILTERS => true,
		self::TITLE_MIN_LENGTH => false,
		self::TITLE_MAX_LENGTH => false,
		self::TITLE_TRUNCATE => false,
		self::TITLE_TRUNCATE_LENGTH => false,
		self::TITLE_TRUNCATE_TERMINATOR => ' ...',

		self::EXCERPT_SEARCH_TAGS => array( 
			'meta og:description', 
			'meta description', 
			'article section',
			'p' 
		), 
		self::EXCERPT_SEO_TAGS_IGNORE_FILTERS => true,
		self::EXCERPT_MIN_LENGTH => 50, 
		self::EXCERPT_MAX_LENGTH => false, 
		self::EXCERPT_TRUNCATE => true, 
		self::EXCERPT_TRUNCATE_LENGTH => 320, 
		self::EXCERPT_TRUNCATE_TERMINATOR => ' ...', 
		self::EXCERPT_LINKIFY => true,

		self::THUMBS_SEO_TAGS_IGNORE_FILTERS => false,
		self::THUMBS_FOUND_STOP_COUNT => false, 
		self::THUMBS_MIN_WIDTH => 100, 
		self::THUMBS_MIN_HEIGHT => 100, 
		self::THUMBS_MAX_WIDTH => false, 
		self::THUMBS_MAX_HEIGHT => false, 
		self::THUMBS_MAX_TRIES => 10, 
		self::THUMBS_MIN_SIZE => false, 
		self::THUMBS_MAX_SIZE => false, 
		self::THUMBS_MIMETYPES_INCLUDE => array( 
			'image/jpeg', 
			'image/png', 
			'image/x-ms-bmp', 
			'image/gif' 
		), 
		self::THUMBS_MIMETYPES_EXCLUDE => array(), 
		self::THUMBS_EXTENSIONS_INCLUDE => array(), 
		self::THUMBS_EXTENSIONS_EXCLUDE => array(), 
		self::THUMBS_URL_BLACKLIST => array( 
			'da\.feedsportal\.com.*', 
			'.*commindo-media-ressourcen\.de.*',  // smashing magazine ads
			'api\.tweetmeme\.com.*', 
			'.*wp-digg-this.*', 
			'.*doubleclick\.net.*' 
		), 

		self::FAVICON_MIN_WIDTH => false, 
		self::FAVICON_MIN_HEIGHT => false, 
		self::FAVICON_MAX_WIDTH => false, 
		self::FAVICON_MAX_HEIGHT => false, 
		self::FAVICON_MAX_TRIES => 10, 
		self::FAVICON_MIN_SIZE => false, 
		self::FAVICON_MAX_SIZE => false, 
		self::FAVICON_MIMETYPES_INCLUDE => array( 
			'image/x-icon', 
			'image/jpeg', 
			'image/png', 
			'image/x-ms-bmp', 
			'image/gif' 
		), 
		self::FAVICON_MIMETYPES_EXCLUDE => array(), 
		self::FAVICON_EXTENSIONS_INCLUDE => array(), 
		self::FAVICON_EXTENSIONS_EXCLUDE => array() 
	);

	/**
	 * @var		array
	 * @access	protected
	 */
	protected $_config = array();

	
	/**
	 * Class constructor
	 *
	 * @param	$cfg null|array
	 */
	public function __construct( $cfg = null )
	{
		$cfg = $cfg !== null ? array_merge( static::$_defaults, $cfg ) : static::$_defaults;
		$this->set( $cfg );
	} // __construct }}}

	/**
	 * Retrieve configuration parameters
	 *
	 * @param	string $var, $varX
	 * @return	mixed
	 */
	public function get()
	{
		if (func_num_args() === 0) {
			return $this->_config;
		}

		$args = func_get_args();
		if (func_num_args() === 1) {
			if (is_array( $args[0] )) {
				$args = $args[0];
			} else {
				return isset( $this->_config[$args[0]] ) ? $this->_config[$args[0]] : null;
			}
		}

		$return = array();
		foreach ( $args as $a ) {
			$return[$a] = isset( $this->_config[$a] ) ? $this->_config[$a] : null;
		}
		return $return;
	} // get }}}

	/**
	 * Set configuration parameters
	 *
	 * @param	array|string $var
	 * @param	mixed $value		optional
	 */
	public function set()
	{
		$args = func_get_args();
		$argc = func_num_args();

		if ($argc != 1 && $argc != 2) {
			throw new \InvalidArgumentException( 'Wrong number of parameters' );
		}

		if ($argc === 1 && is_array( $args[0] )) {
			$vars = $args[0];
		} elseif ($argc === 2) {
			$vars = array_combine( array( $args[0] ), array( $args[1] ) );
		}

		foreach ( $vars as $var => $value ) {
			if (array_key_exists( $var, static::$_defaults )) {
				$this->_config[$var] = $value;
			}
		}
	} // set }}}

	/**
	 * Reset configuration to it's initial state
	 */
	public function reset()
	{
		$this->set( static::$_defaults );
	} // reset }}}
}

