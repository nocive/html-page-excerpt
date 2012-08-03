<?php

/**
 * Text class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	Text
 */
namespace HTMLPageExcerpt;

class Text extends Object
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
	public function __construct( $str, $sanitize = true, $config = null )
	{
		parent::__construct( $config );

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

		$this->content = Util::substrw( $this->content, $length, $terminator );
		return $this->content;
	} // _truncate }}}


	/**
	 * Enter description here ...
	 *
	 * @param	array $criteria					possible criterias: min_length, max_length
	 * @throws	\InvalidArgumentException
	 * @return	bool
	 */
	public function matches( $criteria )
	{
		if (! is_array( $criteria )) {
			throw new \InvalidArgumentException( 'Wrong argument type, criteria must be an array' );
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
		//Util::hexDump( $str );

		// add a space if a tag is present right next to a word
		$str = preg_replace( '@([^\s]+)(<[^>]+>)@', '\\1 \\2', $str );

		//$str = strip_tags( html_entity_decode( $str, ENT_QUOTES, HTML_PageExcerpt_Settings::get( 'encoding' ) ) );

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
		$target = ! empty( $target ) ? " target=\"$target\"" : '';
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
