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
		self::FIELD_TITLE,
		self::FIELD_EXCERPT,
		self::FIELD_THUMBS,
		self::FIELD_FAVICON
	);

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
	}
}
