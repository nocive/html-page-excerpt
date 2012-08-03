<?php

namespace HTMLPageExcerpt;

class Log
{
	const LEVEL_DEBUG = 'debug';;
	const LEVEL_NOTICE = 'notice';
	const LEVEL_WARNING = 'warning';
	const LEVEL_ERROR = 'error';
	const LEVEL_CRITICAL = 'critical';

	protected static $_levels = array(
		self::LEVEL_DEBUG,
		self::LEVEL_NOTICE,
		self::LEVEL_WARNING,
		self::LEVEL_ERROR,
		self::LEVEL_CRITICAL
	);

	/**
	 * Enter description here ...
	 * 
	 * @param	string $logfile
	 * @param	string $str
	 * @param	string $level
	 * @param	bool $appendNewLine
	 * @throws	\HTMLPageExcerpt\FatalException
	 * @throws	\HTMLPageExcerpt\FileReadWriteException
	 * @return	bool
	 */
	public static function write( $logfile, $str, $level, $appendNewline = true )
	{
		if (! in_array( $level, self::_levels)) {
			throw new FatalException( "Invalid log level specified '$level'" );
		}
		$str = date( 'M  d H:i:s' ) . ' ' . __CLASS__ . ": $level >> $str" . ($appendNewline ? "\n" : '');
		if (false === @file_put_contents( $logfile, $str, FILE_APPEND )) {
			throw new FileReadWriteException( "Error writing to logfile '$logfile'" );
		}
		return true;
	} // write }}}
}
