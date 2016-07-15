<?php
/**
 * Created by PhpStorm.
 * User: fanat
 * Date: 09.07.2016
 * Time: 14:10
 */


namespace CrazyFactory\Data\Test;

use CrazyFactory\Core\Interfaces\ISerializer;
use CrazyFactory\Data\Serializers\Base\SerializerBase;


/*
 * Basic Serializer implementation. Will return fixed strings on serialize() and restore(). Compare to these to check if a serializer has been applied.
 */
class SimpleSerializer extends SerializerBase {

    public static function className()
    {
        return get_called_class();
    }

    const RESTORE_RETURN_VALUE = "HAS_BEEN_RESTORED";
    const SERIALIZE_RETURN_VALUE = "HAS_BEEN_SERIALIZED";

    function serialize($input)
    {
        return self::SERIALIZE_RETURN_VALUE;
    }

    function restore($input)
    {
        return self::RESTORE_RETURN_VALUE;
    }
}


class SerializerBaseTest extends \PHPUnit_Framework_TestCase {
    function testInterfaceInheritance() {
        $hasInterface = in_array('CrazyFactory\Core\Interfaces\ISerializer', class_implements(SimpleSerializer::className()));
        $this->assertTrue($hasInterface, 'is missing ISerializer interface');
        // todo PHP 5.5 use SerializerBase::class instanceof ISerializer
    }

    // Tests if serializeEach correctly returns the same result 
    function testSerializeEach() {
        $data = array(
            false,
            0,
            'bob'
        );
        $expected = array(
            SimpleSerializer::SERIALIZE_RETURN_VALUE,
            SimpleSerializer::SERIALIZE_RETURN_VALUE,
            SimpleSerializer::SERIALIZE_RETURN_VALUE
        );

        $obj = new SimpleSerializer();
        $result = $obj->serializeEach($data);

        $this->assertEquals($expected, $result);
    }


    function testRestoreEach() {
        $data = array(
            false,
            0,
            'bob'
        );
        $expected = array(
            SimpleSerializer::RESTORE_RETURN_VALUE,
            SimpleSerializer::RESTORE_RETURN_VALUE,
            SimpleSerializer::RESTORE_RETURN_VALUE
        );

        $obj = new SimpleSerializer();
        $result = $obj->restoreEach($data);

        $this->assertEquals($expected, $result);
    }
}