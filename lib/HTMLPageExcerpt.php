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
 * @version  0.1
 * @link     https://github.com/nocive/html-page-excerpt/
 *
 *************************************************************************************************/

namespace HTMLPageExcerpt;
require_once( __DIR__ . '/Bootstrap/Bootstrap.php' );

class HTMLPageExcerpt extends Base
{
	protected $_config;

	/**
	 * Enter description here ...
	 * 
	 * @param	string $source		optional
	 * @param	array|Config $config	optional
	 * @return	void
	 */
	public function __construct( $source = null, $config = null )
	{
		Bootstrap::checkRequirements();

		if ($config instanceof Config) {
			$this->_config = $config;
		} else {
			$this->_config = new Config( $config );
		}

		if (null !== $source) {
			$this->load( $source );
		}
	}
}
