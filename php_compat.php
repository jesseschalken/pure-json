<?php

if (!defined('JSON_ERROR_UTF8')) {
    define('JSON_ERROR_UTF8', 5);
}

if (!defined('JSON_ERROR_RECURSION')) {
    define('JSON_ERROR_RECURSION', 6);
}

if (!defined('JSON_ERROR_INF_OR_NAN')) {
    define('JSON_ERROR_INF_OR_NAN', 7);
}

if (!defined('JSON_ERROR_UNSUPPORTED_TYPE')) {
    define('JSON_ERROR_UNSUPPORTED_TYPE', 8);
}

if (!defined('JSON_ERROR_INVALID_PROPERTY_NAME')) {
    define('JSON_ERROR_INVALID_PROPERTY_NAME', 9);
}

if (!defined('JSON_ERROR_UTF16')) {
    define('JSON_ERROR_UTF16', 10);
}

if (!function_exists('json_last_error_msg')) {
    /**
     * @return string
     */
    function json_last_error_msg() {
        return \PureJSON\JSON::getErrorMessage(json_last_error());
    }
}

