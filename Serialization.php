<?php

namespace PureJSON;

abstract class Serializer {
    /**
     * @param mixed $value
     * @param bool  $pretty
     * @return string
     */
    public function serialize($value, $pretty = false) {
        return JSON::encode($this->serializeOne($value), true, $pretty);
    }

    /**
     * @param string $json
     * @return mixed
     */
    public function unserialize($json) {
        return $this->unserializeOne(JSON::decode($json, true));
    }

    /**
     * @param mixed $json
     * @return mixed
     */
    private function unserializeOne($json) {
        if (is_array($json)) {
            if ($this->isAssoc($json)) {
                $props = $this->unserializeMany($json);
                $type  = $props['@type'];
                unset($props['@type']);
                if ($type === 'map') {
                    $map = array();
                    foreach ($props['pairs'] as $pair) {
                        $map[$pair[0]] = $pair[1];
                    }
                    return $map;
                } else {
                    return $this->unserialize_($type, $props);
                }
            } else {
                return $this->unserializeMany($json);
            }
        } else {
            return $json;
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws SerializationException
     */
    private function serializeOne($value) {
        if (is_scalar($value) || is_null($value)) {
            return $value;
        } else if (is_array($value)) {
            if ($this->isAssoc($value)) {
                $pairs = array();
                foreach ($value as $k => $v) {
                    $pairs[] = array($k, $v);
                }
                return $this->serializeMany(array(
                    '@type' => 'map',
                    'pairs' => $pairs,
                ));
            } else {
                return $this->serializeMany($value);
            }
        } else if ($value instanceof Serializable) {
            return $this->serializeMany(array_replace(
                $value->getProps(),
                array('@type' => $value->getType())
            ));
        } else {
            throw new SerializationException('Cannot serialize ' . var_export($value, true));
        }
    }

    private function serializeMany(array $values) {
        $jsons = array();
        foreach ($values as $k => $value) {
            $jsons[$k] = $this->serializeOne($value);
        }
        return $jsons;
    }

    private function unserializeMany(array $jsons) {
        $values = array();
        foreach ($jsons as $k => $json) {
            $values[$k] = $this->unserializeOne($json);
        }
        return $values;
    }

    private function isAssoc(array $array) {
        $i = 0;
        foreach ($array as $k => $v) {
            if ($k !== $i++) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $type
     * @param array  $props
     * @return Serializable
     */
    protected abstract function unserialize_($type, $props);
}

interface Serializable {
    /** @return string */
    public function getType();

    /** @return array */
    public function getProps();
}

class SerializationException extends \Exception {
}
