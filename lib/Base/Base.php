<?php

/**
 * Bootstrap class
 *
 * @package	HTMLPageExcerpt
 * @subpackage	Base
 */
namespace HTMLPageExcerpt;

abstract class Base
{
	/**
	 * @var		Config
	 * @access	protected
	 */
	protected $_config;


	public function __construct( $config )
	{
		if ($config instanceof Config) {
			$this->_config = $config;
		} else {
			$this->_config = new Config( $config );
		}
	} // __construct }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	string $str
	 * @param	string $level
	 * @param	string|null $logfile
	 * @return	bool
	 */
	public function log( $str, $level = Log::LEVEL_DEBUG, $logfile = null )
	{
		$config = $this->_config;
		$logfile = $logfile === null ? $config->get( $config::LOGFILE ) : $logfile;

		if ($config->get( $config::LOG ) && ! empty( $logfile )) {
			return Log::write( $logfile, $str, $level );
		}
		return false;
	} // log }}}
}
