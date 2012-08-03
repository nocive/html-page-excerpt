<?php

/**
 * Utilities class
 *
 * @package	HTML_PageExcerpt
 * @subpackage	Utils
 */
namespace HtmlPageExcerpt;

class Util
{
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
	 * @param	string $prefix		optional
	 * @param	bool $touch		optional
	 * @throws	FileReadWriteException
	 * @return	string
	 */
	public static function tempFilename( $prefix = '', $touch = false )
	{
		$tmpname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . md5( uniqid( microtime( true ), true ) );
		if ($touch && false === @touch( $tmpname )) {
			throw new FileReadWriteException( "Unable to create filename '$tmpname', check permissions" );
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
		return \Mimex::mimetypeToExtension( $mimetype );
	} // mimetypeToExtension }}}


	/**
	 * Enter description here ...
	 *
	 * @param	string $extension
	 * @return	string
	 */
	public static function extensionToMimetype( $extension )
	{
		return \Mimex::extensionToMimetype( $extension );
	} // extensionToMimetype }}}


	/**
	 * Enter description here ...
	 *
	 * @param	string $filename
	 * @return	string
	 */
	public static function fileDetectMimetype( $filename )
	{
		return \Mimex::mimetype( $filename, true );
	} // fileDetectMimetype }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	DOMNode
	 * @throws	\InvalidArgumentException
	 * @return	string
	 */
	public static function DOMinnerHTML( $element )
	{
		if (! $element instanceof \DOMNode) {
			throw new \InvalidArgumentException( 'Supplied element is not a DOMNode instance' );
		}

		$innerHTML = '';
		$children = $element->childNodes;
		foreach ( $children as $child ) {
			$tmpDom = new \DOMDocument();
			$tmpDom->appendChild( $tmpDom->importNode( $child, true ) );
			$innerHTML .= trim( $tmpDom->saveHTML() );
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
