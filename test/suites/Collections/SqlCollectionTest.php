<?php
/**
 * @license see LICENSE
 */

namespace CrazyFactory\Core\Test;

use CrazyFactory\Core\Collections\SqlCollection;
use CrazyFactory\Core\Models\Model;


class SampleCollection extends SqlCollection {
   function getTableName() {
       return $this->_tableName;
   }
}


class SqlCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function providerForDetermineTableFromClassName() {
        return [
            [null, null],
            ['User', 'Users'],
            ['Users', 'Users'],
            ['UserCollection', 'Users'],
            ['UsersCollection', 'Users'],
            ['UserSet', 'Users'],
            ['UsersSet', 'Users'],
            ['UserTable', 'Users'],
            ['UsersTable', 'Users'],
            ['Sets', 'Sets'],
            ['Collections', 'Collections'],
            ['CrazyFactory\Core\Test\SampleCollection', 'Samples']
        ];
    }

    /**
     * @dataProvider providerForDetermineTableFromClassName
     *
     * @param string $className
     * @param string $expected
     */
    public function testDetermineTableFromClassName($className, $expected) {
        $this->assertEquals($expected, SqlCollection::determineTableFromClassName($className));
    }
    
    public function testDetermineTableFromClassName_WithSampleCollectionClass() {
        $obj = new SampleCollection(Model::class);
        $this->assertEquals('Samples', $obj->getTableName());
    }

}