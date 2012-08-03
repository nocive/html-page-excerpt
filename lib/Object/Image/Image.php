<?php

/**
 * Image class
 *
 * @package	HTMLPageExcerpt
 * @subpackage	Image
 */
namespace HTMLPageExcerpt;

class Image extends Object
{
	/**
	 * @constant string
	 */
	const TEMPFILE_PREFIX = 'peimg_';

	/**
	 * @constant string
	 */
	const FIELD_URL = 'url';

	/**
	 * @constant string
	 */
	const FIELD_WIDTH = 'width';

	/**
	 * @constant string
	 */
	const FIELD_HEIGHT = 'height';

	/**
	 * @constant string
	 */
	const FIELD_MIMETYPE = 'mimetype';

	/**
	 * @constant string
	 */
	const FIELD_EXTENSION = 'extension';

	/**
	 * @constant string
	 */
	const FIELD_SIZE = 'size';

	/**
	 * @var		Url
	 * @access	public
	 */
	public $url;

	/**
	 * @var		int
	 * @access	public
	 */
	public $width;

	/**
	 * @var		int
	 * @access	public
	 */
	public $height;

	/**
	 * @var		string
	 * @access	public
	 */
	public $mimetype;

	/**
	 * @var		string
	 * @access	public
	 */
	public $extension;

	/**
	 * @var		int
	 * @access	public
	 */
	public $size;

	/**
	 * @var		array
	 * @access	protected
	 */
	protected $_fields = array( 
		self::FIELD_URL,
		self::FIELD_WIDTH,
		self::FIELD_HEIGHT,
		self::FIELD_MIMETYPE,
		self::FIELD_EXTENSION,
		self::FIELD_SIZE
	);

	/**
	 * @var		bool
	 * @access	protected
	 */
	protected $_identified = false;

	/**
	 * @var		string
	 * @access	protected
	 */
	protected $_tmpfilename;


	/**
	 * Enter description here ...
	 * 
	 * @param	string $url
	 * @param	bool $identify	optional
	 */
	public function __construct( $url, $identify = false, $config = null )
	{
		parent::__construct( $config );

		$this->url = new Url( $url, $sanitize = true, $fetch = false, $this->_config );
		if ($identify) {
			$this->identify();
		}
	} // __construct }}}


	/**
	 * Enter description here ...
	 */
	public function __destruct()
	{
		if ($this->_tmpfilename) {
			@unlink( $this->_tmpfilename );
		}
	} // __destruct }}}


	/**
	 * Enter description here ...
	 * 
	 * @throws	InvalidImageFileException
	 * @throws	FatalException
	 * @return	void
	 */
	public function identify()
	{
		if ($this->_identified) {
			return;
		}

		$this->url->fetch();
		$this->_tmpfilename = Util::tempFilename( self::TEMPFILE_PREFIX );
		$this->url->save( $this->_tmpfilename );
		// free some memory
		unset( $this->url->content );

		$this->mimetype = Util::fileDetectMimetype( $this->_tmpfilename );
		if (strpos( $this->mimetype, 'image/' ) !== 0) {
			throw new InvalidImageFileException( 'File is not a valid image' );
		}

		$this->size = filesize( $this->_tmpfilename );
		if (false === ($imginfo = @getimagesize( $this->_tmpfilename ))) {
			throw new FatalException( "Error calling getimagesize() on image '{$this->_tmpfilename}'" );
		}
		$this->width = $imginfo[0];
		$this->height = $imginfo[1];
		// use image_type_to_extension because it's cheaper
		$this->extension = image_type_to_extension( $imginfo[2], false );

		$this->_identified = true;
	} // identify }}}


	/**
	 * Enter description here ...
	 *
	 * @param	$criteria			Possible criterias: mimetypes_include, mimetypes_exclude, extensions_include, extensions_exclude
	 *						min_width, max_width, min_height, max_height, min_size, max_size
	 * @throws	\InvalidArgumentException
	 * @throws	FatalException
	 * @return	bool
	 */
	public function matches( $criteria )
	{
		if (! is_array( $criteria )) {
			throw new \InvalidArgumentException( 'Wrong argument type, criteria must be an array' );
		}

		if (! $this->_identified) {
			$this->identify();
		}

		extract( $criteria );

		if ((! empty( $mimetypes_exclude ) || ! empty( $mimetypes_include )) && (! empty( $extensions_exclude ) || ! empty( $extensions_include ))) {
			throw new FatalException( 'Can\'t do mimetypes include/exclude together with extensions include/exclude, please use only one of both' );
		}

		if (! empty( $extensions_include )) {
			if (empty( $this->extension )) {
				throw new FatalException( 'Extension is empty can\'t do matching' );
			}

			$mimetypes_include = array();
			foreach ( $extensions_include as $ext ) {
				$mime = Util::extensionToMimetype( $ext );
				if (! empty( $mime )) {
					$mimetypes_include[] = $mime;
				}
			}
		}

		if (! empty( $extensions_exclude )) {
			if (empty( $this->extension )) {
				throw new FatalException( 'Extension is empty can\'t do matching' );
			}

			$mimetypes_exclude = array();
			foreach ( $extensions_exclude as $ext ) {
				$mime = Util::extensionToMimetype( $ext );
				if (! empty( $mime )) {
					$mimetypes_exclude[] = $mime;
				}
			}
		}

		if (! empty( $mimetypes_include )) {
			if (empty( $this->mimetype )) {
				throw new FatalException( 'Mimetype is empty can\'t do matching' );
			}
			if (! is_array( $mimetypes_include )) {
				$mimetypes_include = (array) $mimetypes_include;
			}

			if (! in_array( $this->mimetype, $mimetypes_include )) {
				return false;
			}
		}

		if (! empty( $mimetypes_exclude )) {
			if (empty( $this->mimetype )) {
				throw new FatalException( 'Mimetype is empty can\'t do matching' );
			}
			if (! is_array( $mimetypes_exclude )) {
				$mimetypes_exclude = (array) $mimetypes_exclude;
			}

			if (in_array( $this->mimetype, $mimetypes_exclude )) {
				return false;
			}
		}

		if (empty( $this->width ) && (! empty( $min_width ) || ! empty( $max_width ))) {
			throw new FatalException( 'Width is empty can\'t do matching' );
		}

		if (empty( $this->height ) && (! empty( $min_height ) || ! empty( $max_height ))) {
			throw new FatalException( 'Height is empty can\'t do matching' );
		}

		return 	(empty( $min_width ) || $this->width >= $min_width) && 
			(empty( $max_width ) || $this->width <= $max_width) && 
			(empty( $min_height ) || $this->height >= $min_height) && 
			(empty( $max_height ) || $this->height <= $max_height) && 
			(empty( $min_size ) || $this->size >= $min_size) && 
			(empty( $max_size ) || $this->size <= $max_size);
	} // matches }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	mixed $fields	array, string or null for all fields
	 * @return	mixed
	 */
	public function info( $fields = null )
	{
		if (! $this->_identified) {
			$this->identify();
		}

		if ($fields !== null && ! is_array( $fields )) {
			$fields = (array) $field;
		}

		$getAll = ($fields === null || $fields === array( 
				'*' 
		));

		$data = array();
		foreach ( $this->_fields as $f ) {
			if ($getAll || in_array( $f, $fields )) {
				// typecast to string url class
				$data[$f] = ($f === self::FIELD_URL) ? (string) $this->{$f} : $this->{$f};
			}
		}

		return $data;
	} // info }}}


	/**
	 * Enter description here ...
	 * 
	 * @param	string $path
	 * @param	int $perms
	 * @return	void
	 */
	public function save( $path, $perms = 0666 )
	{
		return $this->url->save( $path, $perms );
	} // save }}}


	/**
	 * Enter description here ...
	 * 
	 * @throws	FileReadWriteException
	 * @return	string
	 */
	public function content()
	{
		if (! $this->_identified) {
			$this->identify();
		}

		if (false === ($content = @file_get_contents( $this->_tmpfilename ))) {
			throw new FileReadWriteException( "Could not read contents of file '{$this->_tmpfilename}'" );
		}
		return $content;
	} // content }}}


	/**
	 * This is required because array_unique typecasts to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->url;
	} // __toString }}}
}
