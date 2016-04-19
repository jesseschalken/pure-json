<?php

namespace PureJSON;

class JSONTest extends \PHPUnit_Framework_TestCase {
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
        for ($i = 0; $i < 6; $i++) {
            $value1 = array_fill(0, 7, $value1);
        }

        $t = microtime(true);

        $json   = JSON::encode($value1, true);
        $value2 = JSON::decode($json, true);

        $t = microtime(true) - $t;

        printf(__METHOD__ . " took %.3fs\n", $t);

        self::assertEquals($value2, $value1);
    }
}
