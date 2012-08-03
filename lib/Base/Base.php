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
	}	

	/**
	 * Enter description here ...
	 * 
	 * @param	string $str
	 * @param	string $level
	 * @return	bool
	 */
	public function log( $str, $level = Log::LEVEL_DEBUG, $logfile = null )
	{
		$cfgClass = $this->_config;
		$logfile = $logfile !== null ? $this->_config->get( $cfgClass::LOGFILE ) : $logfile;

		if ($this->_config->get( $cfgClass::LOG ) && ! empty( $logfile )) {
			return Log::write( $logfile, $str, $level );
		}
		return false;
	}
}
