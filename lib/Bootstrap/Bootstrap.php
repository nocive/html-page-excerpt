<?php

/**
 * Bootstrap class
 *
 * @package	HTMLPageExcerpt
 * @subpackage	Bootstrap
 */
namespace HTMLPageExcerpt;

class Bootstrap
{
	const PHP_MIN_VERSION = '5.3.0';

	const PHPEXT_CURL = 'curl';
	const PHPEXT_DOM = 'dom';
	const PHPEXT_ICONV = 'iconv';
	const PHPEXT_FILEINFO = 'fileinfo';

	const CLASS_EXTENSION = '.php';

	public static $classmap = array(
		'HTMLPageExcerpt' => 'HTMLPageExcerpt',
		'Base' => 'Base/Base',
		'Config' => 'Config/Config',
		'Object' => 'Object/Object',
		'Util' => 'Util/Util'
	);

	/**
	 * @var		array
	 * @access	public
	 */
	protected static $_extensions = array( 
		self::PHPEXT_CURL,
		self::PHPEXT_DOM,
		self::PHPEXT_ICONV,
		self::PHPEXT_FILEINFO
	);

	/**
	 * @var array
	 * @access public
	 */
	protected static $_dependencies = array(
		'Externals/Mimex/Mimex.php'
	);

	/**
	 * Check class php requirements
	 *
	 * @throws FatalException
	 */
	public static function checkRequirements()
	{
		static $checked = false;

		if (! $checked) {
			if (version_compare( PHP_VERSION, self::PHP_MIN_VERSION ) < 0) {
				throw new FatalException( 'This library requires PHP version >= ' . self::PHP_MIN_VERSION );
			}

			foreach ( self::$_extensions as $ext ) {
				if (! extension_loaded( $ext )) {
					throw new FatalException( "Required extension '$ext' is not loaded!" );
				}
			}

			foreach ( self::$_dependencies as $dep ) {
                                if (false === @include_once ($dep)) {
                                        throw new FatalException( "Required dependency '$dep' was not found!" );
                                }
                        }

			$checked = true;
		}
	} // checkRequirements }}}


	/**
	 * Class autoloader
	 *
	 * @param string $class	class name
	 */
	public static function autoload( $class )
	{
		$class = str_replace( __NAMESPACE__ . '\\', '', $class );
		if (isset( self::$classmap[$class] )) {
			include_once( __DIR__ . '/../' . self::$classmap[$class] . self::CLASS_EXTENSION );
		}
	} // autoload }}}


	/**
	 * Autoloader register helper
	 */
	public static function autoloadRegister()
	{
		spl_autoload_register( __CLASS__ . '::autoload' );
	} // autoloadRegister }}}
}

Bootstrap::autoloadRegister();

