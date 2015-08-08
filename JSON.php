<?php

namespace PureJSON;

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
            throw new JSONException("Inf and NaN cannot be JSON encoded", JSON_ERROR_INF_OR_NAN);
        } else if (is_object($value) || is_resource($value)) {
            throw new JSONException("Type is not supported", JSON_ERROR_UNSUPPORTED_TYPE);
        } else if (is_array($value)) {
            foreach ($value as $v)
                self::checkValue($v);
        }
    }

    private static function checkError() {
        if (json_last_error() !== JSON_ERROR_NONE)
            throw new JSONException(json_last_error_msg(), json_last_error());
    }

    /**
     * Do something do all the strings in $value
     * @param mixed    $value
     * @param callable $callback
     * @return mixed
     */
    private static function mapStrings($value, callable $callback) {
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
}

