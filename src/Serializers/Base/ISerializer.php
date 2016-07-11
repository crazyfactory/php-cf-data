<?php
/**
 * Created by PhpStorm.
 * User: fanat
 * Date: 08.07.2016
 * Time: 17:13
 */


namespace CrazyFactory\Core\Serializers\Base;

interface ISerializer {

    /**
     * @param $input
     * @return mixed
     */
    function serialize($input);

    /**
     * @param $input
     * @return mixed
     */
    function restore($input);

    /**
     * @param $list
     * @return mixed
     */
    function serializeEach($list);

    /**
     * @param $list
     * @return mixed
     */
    function restoreEach($list);
}