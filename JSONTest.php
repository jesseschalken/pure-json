<?php

namespace PureJSON;

abstract class SerializeTest implements Serializable {
    public static function jsonCreate(array $props) {
        return new static();
    }

    public function jsonProps() {
        return array();
    }
}

final class SerializeTest1 extends SerializeTest {
    public static function jsonType() {
        return 'test1';
    }
}

final class SerializeTest2 extends SerializeTest {
    public static function jsonType() {
        return 'test2';
    }
}

final class SerializeTest3 extends SerializeTest {
    public static function jsonType() {
        return 'test3';
    }
}

final class JSONTest extends \PHPUnit_Framework_TestCase {
    /**
     * @expectedException \PureJSON\JSONException
     * @expectedExceptionCode    8
     * @expectedExceptionMessage Type is not supported
     */
    public function testObject() {
        JSON::encode(array(new \stdClass));
    }

    /**
     * @expectedException \PureJSON\JSONException
     * @expectedExceptionCode    8
     * @expectedExceptionMessage Type is not supported
     */
    public function testResource() {
        JSON::encode(array(fopen('php://memory', 'rb')));
    }

    /**
     * @expectedException \PureJSON\JSONException
     * @expectedExceptionCode    7
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testINF() {
        JSON::encode(array(INF));
    }

    /**
     * @expectedException \PureJSON\JSONException
     * @expectedExceptionCode    7
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testNegINF() {
        JSON::encode(array(-INF));
    }

    /**
     * @expectedException \PureJSON\JSONException
     * @expectedExceptionCode    7
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testNAN() {
        JSON::encode(array(NAN));
    }

    public function testEmpty() {
        self::assertEquals(JSON::encode(array()), '[]');
    }

    public function testReversible() {
        $values = array(
            array('j'),
            array(),
            0,
            0.0,
            'lololololo',
            '',
            null,
            true,
            false,
            -1.0 / 3.0,
            array('a' => 0, 'z' => 1),
            array('z' => 0, 'a' => 1),
            M_PI,
        );
        self::assertEquals(JSON::decode(JSON::encode($values)), $values);
    }

    /**
     * @expectedException \PureJSON\JSONException
     * @expectedExceptionCode    5
     * @expectedExceptionMessage Malformed UTF-8 characters, possibly incorrectly encoded
     */
    public function testBinaryFail() {
        $value = array(join(' ', range("\x00", "\xFF")));
        self::assertEquals(JSON::decode(JSON::encode($value)), $value);
    }

    public function testBinaryOkay() {
        $value = array(join(' ', range("\x00", "\xFF")));
        self::assertEquals(JSON::decode(JSON::encode($value, true), true), $value);
    }

    public function testUnicode() {
        $value = array("־׀׃׆אבגדהוזחטיךכלםמןנסעףפץצקרשתװױײ׳״");

        // Just make sure this is in fact utf-8
        self::assertNotEquals(utf8_encode($value[0]), $value[0]);

        self::assertEquals(JSON::decode(JSON::encode($value)), $value);
    }

    public function testPerformance() {
        ini_set('memory_limit', '-1');

        $value1 = 'hello';
        for ($i = 0; $i < 5; $i++) {
            $value1 = array_fill(0, 7, $value1);
        }

        $t = microtime(true);

        $json   = JSON::encode($value1, true);
        $value2 = JSON::decode($json, true);

        $t = microtime(true) - $t;

        printf(__METHOD__ . " took %.3fs\n", $t);

        self::assertEquals($value2, $value1);
    }

    public function testPretty() {
        self::assertEquals(
            JSON::encode(array(
                'erjghb'  => array(
                    845,
                    34,
                    234,
                    63,
                    true,
                    null,
                ),
                'aergerg' => array(),
            ), false, true),
            <<<'s'
{
    "erjghb": [
        845,
        34,
        234,
        63,
        true,
        null
    ],
    "aergerg": []
}
s
        );
    }

    public function testSerialization() {
        $value1 = array(new SerializeTest1(), new SerializeTest2(), 100);
        $value2 = JSON::deserialize(JSON::serialize($value1), array(
            SerializeTest1::class,
            SerializeTest2::class,
            SerializeTest3::class,
        ));
        self::assertEquals($value2, $value1);
    }

    /**
     * @throws SerializationException
     * @expectedException \PureJSON\SerializationException
     * @expectedExceptionCode    0
     * @expectedExceptionMessage Type tag 'test1' must be one of: test2, test3
     */
    public function testSerializationError() {
        JSON::deserialize(JSON::serialize(new SerializeTest1()), array(
            SerializeTest2::class,
            SerializeTest3::class,
        ));
    }

    /**
     * @expectedException \PureJSON\SerializationException
     * @expectedExceptionCode    0
     * @expectedExceptionMessage Both 'PureJSON\SerializeTest2' and 'PureJSON\SerializeTest2' use JSON type tag 'test2'
     */
    public function testDeserializeDuplicateClass() {
        JSON::deserialize('null', array(
            SerializeTest2::class,
            SerializeTest2::class,
            SerializeTest3::class,
        ));
    }

    /**
     * @expectedException \PureJSON\SerializationException
     * @expectedExceptionCode    0
     * @expectedExceptionMessage Object is missing @type property
     */
    public function testDeserializeNoTag() {
        JSON::deserialize('{"foo": "bar"}', array());
    }

    /**
     * @expectedException \PureJSON\SerializationException
     * @expectedExceptionCode    0
     * @expectedExceptionMessage Objects must implement PureJSON\Serializable
     */
    public function testSerializeUnserializable() {
        JSON::serialize(new \stdClass());
    }

    /**
     * @expectedException \PureJSON\SerializationException
     * @expectedExceptionCode    0
     * @expectedExceptionMessage Associative arrays are not supported
     */
    public function testSerializeAssocArray() {
        JSON::serialize(array('ergerg' => 5));
    }
}
