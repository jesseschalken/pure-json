<?php

namespace PureJSON;

interface Serializable {
    /** @return string */
    public static function jsonType();
}

final class JSON {
    /**
     * @param mixed $value
     * @param bool  $binary
     * @param bool  $pretty
     * @return string
     */
    public static function serialize($value, $binary = false, $pretty = false) {
        return self::encode(self::_serialize($value), $binary, $pretty);
    }

    /**
     * @param string   $json
     * @param string[] $classes
     * @param bool     $binary
     * @return mixed
     */
    public static function deserialize($json, array $classes, $binary = false) {
        $classMap = array();
        /** @var Serializable $class */
        foreach ($classes as $class) {
            $classMap[$class::jsonType()] = $class;
        }
        return self::_deserialize(self::decode($json, $binary), $classMap);
    }

    private static function _serialize($value) {
        if (is_array($value)) {
            if (self::isAssoc($value)) {
                throw new SerializationException("Associative arrays are not supported");
            } else {
                $result = array();
                foreach ($value as $v) {
                    $result[] = self::_serialize($v);
                }
                return $result;
            }
        } else if (is_object($value)) {
            if ($value instanceof Serializable) {
                $result = array('@type' => $value->jsonType());
                foreach (get_object_vars($value) as $k => $v) {
                    $result[$k] = self::_serialize($v);
                }
                return $result;
            } else {
                throw new SerializationException("Objects must implement PureJSON\\Serializable");
            }
        } else {
            return $value;
        }
    }

    private static function _deserialize($value, array $classMap) {
        if (is_array($value)) {
            if (self::isAssoc($value)) {
                if (!isset($value['@type'])) {
                    throw new SerializationException("Object is missing @type property");
                }
                $type = $value['@type'];
                unset($value['@type']);
                if (!isset($classMap[$type])) {
                    throw new SerializationException("Unknown type '$type' (known types: " . join(', ', array_keys($classMap)) . ")");
                }
                $object = new $classMap[$type]();
                foreach ($value as $k => $v) {
                    $object->$k = self::_deserialize($v, $classMap);
                }
                return $object;
            } else {
                $result = array();
                foreach ($value as $v) {
                    $result[] = self::_deserialize($v, $classMap);
                }
                return $result;
            }
        } else {
            return $value;
        }
    }

    private static function isAssoc(array $array) {
        $i = 0;
        foreach ($array as $k => $_) {
            if ($k !== $i++) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $json   JSON string
     * @param bool   $binary Return a PHP value containing binary/ISO-8859-1 strings instead of UTF-8
     * @return mixed PHP value
     * @throws JSONException
     */
    public static function decode($json, $binary = false) {
        $value = json_decode($json, true);
        self::checkError();
        self::checkValue($value);

        if ($binary) {
            $value = self::mapStrings($value, 'utf8_decode');
        }

        return $value;
    }

    /**
     * @param mixed $value  PHP value
     * @param bool  $binary Interpret strings in the PHP value as binary/ISO-8859-1 instead of UTF-8
     * @param bool  $pretty Whether JSON should be pretty printed (true) or not (false)
     * @return string JSON string
     * @throws JSONException
     */
    public static function encode($value, $binary = false, $pretty = false) {
        $flags = 0;
        if (defined('JSON_PRETTY_PRINT') && $pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }
        if (defined('JSON_UNESCAPED_SLASHES')) {
            $flags |= JSON_UNESCAPED_SLASHES;
        }
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $flags |= JSON_UNESCAPED_UNICODE;
        }
        if (defined('JSON_PRESERVE_ZERO_FRACTION')) {
            $flags |= JSON_PRESERVE_ZERO_FRACTION;
        }

        if ($binary) {
            $value = self::mapStrings($value, 'utf8_encode');
        }

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
            foreach ($value as $v) {
                self::checkValue($v);
            }
        }
    }

    private static function checkError() {
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JSONException(json_last_error_msg(), json_last_error());
        }
    }

    /**
     * Do something do all the strings in $value
     * @param mixed    $value
     * @param callable $callback
     * @return mixed
     */
    private static function mapStrings($value, $callback) {
        if (is_string($value)) {
            return $callback($value);
        } else if (is_array($value)) {
            $result = array();
            foreach ($value as $k => $v) {
                $result[self::mapStrings($k, $callback)] = self::mapStrings($v, $callback);
            }
            return $result;
        } else {
            return $value;
        }
    }
}

final class JSONException extends \Exception {
}

final class SerializationException extends \Exception {
}

