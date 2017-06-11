<?php

namespace HTMLPageExcerpt;

class Util
{
    /**
     * @param  string $str
     * @param  int    $length
     * @param  string $terminator
     * @param  int    $minword
     *
     * @return string
     */
    public static function substrw($str, $length, $terminator = '...', $minword = 3)
    {
        $sub = '';
        $len = 0;

        foreach (explode(' ', $str) as $word) {
            $part = (($sub != '') ? ' ' : '') . $word;
            $sub .= $part;
            $len += strlen($part);

            if (strlen($word) > $minword && strlen($sub) >= $length) {
                break;
            }
        }

        return $sub . (($len < strlen($str)) ? $terminator : '');
    }

    /**
     * @param  string $prefix
     * @param  bool   $touch
     *
     * @return string
     *
     * @throws Exception\FileIOException
     */
    public static function tempFilename($prefix = '', $touch = false)
    {
        $tmpName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . md5(uniqid(microtime(true), true));
        if ($touch && false === @touch($tmpName)) {
            throw new Exception\FileIOException("Unable to create filename '$tmpName', check permissions");
        }

        return $tmpName;
    }

    public static function convertEncoding($str, $from, $to)
    {
        return @iconv($from, $to, $str);
    }

    /**
     * @param  \DOMNode $element
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function DOMinnerHTML(\DOMNode $element)
    {
        $innerHTML = '';
        $children = $element->childNodes;
        foreach ($children as $child) {
            $tmpDom = new \DOMDocument();
            $tmpDom->appendChild($tmpDom->importNode($child, true));
            $innerHTML .= trim($tmpDom->saveHTML());
        }

        return $innerHTML;
    }

    /**
     * @param string $data
     * @param string $newline
     */
    public static function hexDump($data, $newline = "\n")
    {
        static $from = '';
        static $to = '';
        static $width = 16; // number of bytes per line
        static $pad = '.'; // padding for non-visible characters

        if ($from === '') {
            for ($i = 0; $i <= 0xFF; $i++) {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex = str_split(bin2hex($data), $width * 2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $offset = 0;
        foreach ($hex as $i => $line) {
            echo sprintf('%6X', $offset) . ' : ' . implode(' ', str_split($line, 2)) . ' [' . $chars[$i] . ']' . $newline;
            $offset += $width;
        }
    }
}
