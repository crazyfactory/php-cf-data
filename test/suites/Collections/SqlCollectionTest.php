<?php
/**
 * @license see LICENSE
 */

namespace CrazyFactory\Data\Test;

use CrazyFactory\Data\Collections\SqlCollection;


class SampleCollection extends SqlCollection {
   function getTableName() {
       return $this->_tableName;
   }
}


class SqlCollectionTest extends \PHPUnit_Framework_TestCase
{

    // todo: add test cases

}