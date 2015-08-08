<?php

if (!defined('JSON_ERROR_UTF8'))
    define('JSON_ERROR_UTF8', 5);

if (!defined('JSON_ERROR_RECURSION'))
    define('JSON_ERROR_RECURSION', 6);

if (!defined('JSON_ERROR_INF_OR_NAN'))
    define('JSON_ERROR_INF_OR_NAN', 7);

if (!defined('JSON_ERROR_UNSUPPORTED_TYPE'))
    define('JSON_ERROR_UNSUPPORTED_TYPE', 8);

if (!defined('JSON_ERROR_INVALID_PROPERTY_NAME'))
    define('JSON_ERROR_INVALID_PROPERTY_NAME', 9);

if (!defined('JSON_ERROR_UTF16'))
    define('JSON_ERROR_UTF16', 10);

if (!function_exists('json_last_error_msg')) {
    /**
     * @return string
     */
    function json_last_error_msg() {
        static $messages = array(
            JSON_ERROR_NONE                  => "No error",
            JSON_ERROR_DEPTH                 => "Maximum stack depth exceeded",
            JSON_ERROR_STATE_MISMATCH        => "State mismatch (invalid or malformed JSON)",
            JSON_ERROR_CTRL_CHAR             => "Control character error, possibly incorrectly encoded",
            JSON_ERROR_SYNTAX                => "Syntax error",
            JSON_ERROR_UTF8                  => "Malformed UTF-8 characters, possibly incorrectly encoded",
            JSON_ERROR_RECURSION             => "Recursion detected",
            JSON_ERROR_INF_OR_NAN            => "Inf and NaN cannot be JSON encoded",
            JSON_ERROR_UNSUPPORTED_TYPE      => "Type is not supported",
            JSON_ERROR_INVALID_PROPERTY_NAME => "The decoded property name is invalid",
            JSON_ERROR_UTF16                 => "Single unpaired UTF-16 surrogate in unicode escape",
        );

        $error = json_last_error();
        return array_key_exists($error, $messages)
            ? $messages[$error]
            : 'Unknown error';
    }
}

