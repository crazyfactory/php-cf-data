<?php
/**
 * Created by PhpStorm.
 * User: fanat
 * Date: 08.07.2016
 * Time: 17:21
 */

namespace CrazyFactory\Core\Serializers\Base;


abstract class SerializerBase implements ISerializer
{
    function serializeEach($list)
    {
        // Fail if not a list
        if (!is_array($list))
            throw new \InvalidArgumentException();

        // Transform list items
        $out = [];
        foreach ($list as $item) {
            $out[] = $this->serialize($item);
        }

        return $out;
    }

    function restoreEach($list)
    {
        // Fail if not a list
        if (!is_array($list))
            throw new \InvalidArgumentException();

        // Transform list items
        $out = [];
        foreach ($list as $item) {
            $out[] = $this->restore($item);
        }

        return $out;
    }
}