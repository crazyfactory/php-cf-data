<?php

namespace CrazyFactory\Data\Serializers\Base;

use CrazyFactory\Core\Interfaces\ISerializer;

abstract class SerializerBase implements ISerializer
{
	/**
     * @param mixed[] $list
     *
     * @return mixed[]
     */
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


	/**
     * @param mixed[] $list
     *
     * @return mixed[]
     */
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