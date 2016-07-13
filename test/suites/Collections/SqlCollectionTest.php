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

    public function testAdd() {
        // todo: implement test case?
    }

    public function testUpdate() {
        // todo: implement test case?
    }

    public function testRemove() {
        // todo: implement test case?
    }

    public function testCount() {
        // todo: implement test case?
    }
}