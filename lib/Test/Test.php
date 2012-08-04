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
}

