<?php
/**
 * Created by PhpStorm.
 * User: fanat
 * Date: 09.07.2016
 * Time: 14:10
 */


namespace CrazyFactory\Core\Test;

use CrazyFactory\Core\Serializers\Base\ISerializer;
use CrazyFactory\Core\Serializers\Base\SerializerBase;


/*
 * Basic Serializer implementation. Will return fixed strings on serialize() and restore(). Compare to these to check if a serializer has been applied.
 */
class SimpleSerializer extends SerializerBase {

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
        $this->assertTrue(is_subclass_of(SerializerBase::class, ISerializer::class),
            'is missing ISerializer interface');
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