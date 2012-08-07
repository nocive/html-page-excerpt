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
	/**
	 * @constant	string
	 */
	const PHP_MIN_VERSION = '5.3.0';

	/**
	 * @constant	string
	 */
	const PHPEXT_CURL = 'curl';

	/**
	 * @constant	string
	 */
	const PHPEXT_DOM = 'dom';

	/**
	 * @constant	string
	 */
	const PHPEXT_ICONV = 'iconv';

	/**
	 * @constant	string
	 */
	const PHPEXT_FILEINFO = 'fileinfo';

	/**
	 * @constant	string
	 */
	const CLASS_EXTENSION = '.php';

	
	/**
	 * @var		array
	 * @access	public
	 */
	public static $classmap = array(
		'HTMLPageExcerpt' => 'HTMLPageExcerpt',
		'Base' => 'Base/Base',
		'Config' => 'Config/Config',
		'Object' => 'Object/Object',
		'Url' => 'Object/Url/Url',
		'Text' => 'Object/Text/Text',
		'Image' => 'Object/Image/Image',
		'Util' => 'Util/Util',
		'Log' => 'Log/Log',
		'Test' => 'Test/Test',
		'Exception' => 'Exception/Exception',
		'CommunicationException' => 'Exception/Exception',
		'FileReadWriteException' => 'Exception/Exception',
		'InvalidImageFileException' => 'Exception/Exception',
		'FatalException' => 'Exception/Exception'
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
		'Externals/Mimex/mimex.php'
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
			if (version_compare( PHP_VERSION, static::PHP_MIN_VERSION ) < 0) {
				throw new FatalException( 'This library requires PHP version >= ' . static::PHP_MIN_VERSION );
			}

			foreach ( static::$_extensions as $ext ) {
				if (! extension_loaded( $ext )) {
					throw new FatalException( "Required extension '$ext' is not loaded!" );
				}
			}

			foreach ( static::$_dependencies as $dep ) {
                                if (false === @include_once( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $dep )) {
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
		if (isset( static::$classmap[$class] )) {
			include_once( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . static::$classmap[$class] . static::CLASS_EXTENSION );
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
 
