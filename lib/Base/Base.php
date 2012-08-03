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


	/**
	 * Class constructor
	 *
	 * @param	Config|array
	 */
	public function __construct( $config )
	{
		if ($config instanceof Config) {
			$this->_config = $config;
		} else {
			$this->_config = new Config( $config );
		}
	} // __construct }}}

	/**
	 * Get config class instance
	 *
	 * @return	Config
	 */
	public function getConfig()
	{
		return $this->_config;
	} // getConfig }}}

	/**
	 * Set config class instance
	 *
	 * @param	$config Config
	 * @throws	\InvalidArgumentException
	 * @return	Base
	 */
	public function setConfig( $config )
	{
		if (! $config instanceof Config) {
			throw new \InvalidArgumentException( '$config must be an instance of Config' );
		}
		$this->_config = $config;
		return $this;
	} // setConfig }}}

	/**
	 * Enter description here ...
	 * 
	 * @param	string $str
	 * @param	string $level		optional
	 * @param	string|null $logfile	optional
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
