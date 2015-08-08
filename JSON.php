<?php

namespace PureJSON;

if (!defined('JSON_ERROR_UTF8'))
    define('JSON_ERROR_UTF8', 5);

if (!defined('JSON_ERROR_RECURSION'))
    define('JSON_ERROR_RECURSION', 6);

if (!defined('JSON_ERROR_INF_OR_NAN'))
    define('JSON_ERROR_INF_OR_NAN', 7);

if (!defined('JSON_ERROR_UNSUPPORTED_TYPE'))
    define('JSON_ERROR_UNSUPPORTED_TYPE', 8);

final class JSON {
    /**
     * @param string $json   JSON string
     * @param bool   $binary Return a PHP value containing binary/ISO-8859-1 strings instead of UTF-8
     * @return mixed PHP value
     * @throws JSONException
     */
    static function decode($json, $binary = false) {
        $value = json_decode($json, true);
        self::checkError();
        self::checkValue($value);

        if ($binary)
            $value = self::mapStrings($value, 'utf8_decode');

        return $value;
    }

    /**
     * @param mixed $value  PHP value
     * @param bool  $binary Interpret strings in the PHP value as binary/ISO-5591-1 instead of UTF-8
     * @param bool  $pretty Whether JSON should be pretty printed (true) or not (false)
     * @return string JSON string
     * @throws JSONException
     */
    static function encode($value, $binary = false, $pretty = false) {
        $flags = 0;
        if (defined('JSON_PRETTY_PRINT') && $pretty)
            $flags |= JSON_PRETTY_PRINT;
        if (defined('JSON_UNESCAPED_SLASHES'))
            $flags |= JSON_UNESCAPED_SLASHES;
        if (defined('JSON_UNESCAPED_UNICODE'))
            $flags |= JSON_UNESCAPED_UNICODE;
        if (defined('JSON_PRESERVE_ZERO_FRACTION'))
            $flags |= JSON_PRESERVE_ZERO_FRACTION;

        if ($binary)
            $value = self::mapStrings($value, 'utf8_encode');

        self::checkValue($value);
        $json = json_encode($value, $flags);
        self::checkError();
        return $json;
    }

    /**
     * @param mixed $value
     * @throws JSONException
     */
    private static function checkValue($value) {
        if (is_float($value) && !is_finite($value)) {
            throw new JSONException(JSON_ERROR_INF_OR_NAN);
        } else if (is_object($value) || is_resource($value)) {
            throw new JSONException(JSON_ERROR_UNSUPPORTED_TYPE);
        } else if (is_array($value)) {
            foreach ($value as $v)
                self::checkValue($v);
        }
    }

    private static function checkError() {
        $last = json_last_error();
        if ($last !== JSON_ERROR_NONE)
            throw new JSONException($last);
    }

    /**
     * Do something do all the strings in $value
     * @param mixed    $value
     * @param callable $callback
     * @return mixed
     */
    function mapStrings($value, callable $callback) {
        if (is_string($value)) {
            return $callback($value);
        } else if (is_array($value)) {
            $result = array();
            foreach ($value as $k => $v)
                $result[self::mapStrings($k, $callback)] = self::mapStrings($v, $callback);
            return $result;
        } else {
            return $value;
        }
    }

}

final class JSONException extends \Exception {
    /**
     * @param int $code
     */
    function __construct($code) {
        static $messages = array(
            JSON_ERROR_NONE             => 'No error has occurred',
            JSON_ERROR_DEPTH            => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR        => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX           => 'Syntax error',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            JSON_ERROR_RECURSION        => 'One or more recursive references in the value to be encoded',
            JSON_ERROR_INF_OR_NAN       => 'One or more NAN or INF values in the value to be encoded',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
        );

        parent::__construct($messages[$code], $code);
    }
}

