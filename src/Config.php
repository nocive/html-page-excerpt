<?php

namespace HTMLPageExcerpt;

class Config
{
    /** @var array */
    protected $config = array();

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->normalize($config);
        $this->config = $config;
    }

    /**
     * @param string $filename
     *
     * @return static
     */
    public static function fromIni($filename)
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist or is not readable', $filename));
        }

        $config = parse_ini_file($filename, true, INI_SCANNER_RAW);

        return new static($config);
    }

    /**
     * Retrieve configuration parameters
     *
     * @param  string $section
     * @param  string $name
     *
     * @return mixed
     */
    public function get($section, $name = null)
    {
        if (null === $name) {
            return isset($this->config[$section]) ? $this->config[$section] : null;
        }

        return isset($this->config[$section][$name]) ? $this->config[$section][$name] : null;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * @param array &$config
     */
    protected function normalize(array &$config)
    {
        // TODO merge with sensible default values to avoid undefined index warnings
        foreach ($config as $section => $data) {
            foreach ($data as $k => $v) {
                switch ($k) {
                    case 'search_methods':
                    case 'mimetypes_include':
                    case 'mimetypes_exclude':
                    case 'extensions_include':
                    case 'extensions_exclude':
                        $v = $v === "" ? array() : explode(' ', $v);
                        break;
                }

                if (!is_array($v)) {
                    switch ($v) {
                        case 'yes':
                        case 'true':
                        case 'on':
                            $v = true;
                            break;
                        case 'no':
                        case 'false':
                        case 'off':
                            $v = false;
                            break;
                        case 'null':
                            $v = null;
                            break;
                        default:
                            if (preg_match('/^[0-9]+$/', $v)) {
                                $v = (int) $v;
                            } elseif (preg_match('/^[0-9\.]+$/', $v)) {
                                $v = (float) $v;
                            }
                    }
                }
                $config[$section][$k] = $v;
            }
        }
    }
}

