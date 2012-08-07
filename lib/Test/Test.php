<?php

namespace HTMLPageExcerpt;
require_once( __DIR__ . '/../HTMLPageExcerpt.php' );

/**
 * Test class
 *
 * @package	HTMLPageExcerpt
 * @subpackage	Test
 */
class Test
{
	/**
	 * @var		HTMLPageExcerpt
	 * @access	public
	 */
	public $instance;


	public function __construct( $config = null )
	{
		$this->instance = new HTMLPageExcerpt( null, $config );
	} // __construct }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	array $urls
	 */
	public function run( $urls = null )
	{
		if (is_string( $urls )) {
			$urls = array( $urls );
		}

		foreach ( $urls as $url ) {
			try {
				$this->instance->load( $url );
				echo "$url\n";
				echo str_repeat( '-', 200 ) . "\n";
				//var_dump( $this->instance->get( 'favicon' ) );
				//var_dump( $this->instance->get( array('title', 'excerpt') ) );
				var_dump($this->instance->get('*', true));
				echo "\n\n";
				sleep(1);
			} catch ( CommunicationException $e ) {
				echo ">> Error, skipping\n";
			}
		}
	}
}
