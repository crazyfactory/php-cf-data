<?php
/**
 * @license see LICENSE
 */


// fake df_query function and expose used query
namespace {
    function df_query($query)
    {
        $GLOBALS['last_sql_query'] = $query;
        return 5;
    }

    function get_last_sql()
    {
        return $GLOBALS['last_sql_query'];
    }
}


namespace CrazyFactory\Data\Test
{

    use CrazyFactory\Data\Collections\SqlCollection;
    use CrazyFactory\Data\Models\Base\ModelBase;


    class SampleCollection extends SqlCollection
    {
        function getTableName()
        {
            return $this->_tableName;
        }
    }

    class Example extends ModelBase
    {
        const PRIMARY_KEY = "example_id";

        function __construct($name = null)
        {
            $this->initProperties(array(
                'example_id' => null,
                'name' => $name
            ));
        }
    }


    class Broken extends ModelBase
    {
        const PRIMARY_KEY = false;
    }

    class SqlCollectionTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException \Exception
         */
        public function testConstruct_ThrowsExceptionWithInvalidTableName()
        {
            $obj = new SampleCollection(Example::className(), null, true);
        }

        /**
         * @expectedException \Exception
         */
        public function testConstruct_ThrowsExceptionWithInvalidTablePrimaryKey()
        {
            $obj = new SampleCollection(Example::className(), null, null, true);
        }

        /**
         * @expectedException \Exception
         */
        public function testConstruct_ThrowsExceptionWithInvalidModelPrimaryKey()
        {
            new SampleCollection(Broken::className());
        }

        public function testAdd_withEmptyList()
        {
            $obj = new SampleCollection(Example::className(), null, 'funky_table', 'funky_id');
            $result = $obj->add(array());
            $this->assertNull($result, 'An empty list should return null on add');
        }

        public function testAdd()
        {
            // Create alice and make her dirty by fixing her name
            $alice = new Example('Ali');
            $alice->setPropertyValue('name', 'Alice');
            // Create bob with correct name
            $bob = new Example('Bob');

            $list = array($alice, $bob);

            $obj = new SampleCollection(Example::className(), null, 'funky_table');

            // Add them both to the DB
            // Should return 5 as last insert id. indicating that a query function was called
            $last_insert_id = $obj->add($list);
            $this->assertEquals(5, $last_insert_id);

            // Get last called SQL
            $sql = get_last_sql();

            // Quickly validate the query.
            $this->assertEquals('INSERT INTO `funky_table` (`name`) VALUES ("Alice"), ("Bob");', $sql);

            // Check if IDs have been set
            $this->assertEquals($last_insert_id - 1, $alice->getPropertyValue('example_id'), 'Alice\'s id should be 1 less than the last inserted id');
            $this->assertEquals($last_insert_id, $bob->getPropertyValue('example_id'), 'Bob\'s id should equal to the last inserted id');

            // Dirty state should be false
            $this->assertFalse($alice->isDirty(), 'Alice should not be dirty after add()');
            $this->assertFalse($bob->isDirty(), 'Bob should not be dirty after add()');
        }

        public function testUpdate_withEmptyList()
        {
            $obj = new SampleCollection(Example::className(), null, 'funky_table', 'funky_id');
            $result = $obj->update(array());
            $this->assertNull($result, 'An empty list should return null on update');
        }

        public function testUpdate()
        {
            // Create alice and make her dirty by fixing her name
            $alice = new Example('Ali');
            $alice->setPropertyValue('example_id', 4);
            $alice->setPropertyValue('name', 'Alice');

            // Create non-dirty bob
            $bob = new Example('Bob');
            $bob->setPropertyValue('example_id', 5);
            $bob->resetDirtyState();


            $list = array($alice, $bob);
            $obj = new SampleCollection(Example::className(), null, 'funky_table');

            // Call method and retrieve query
            $obj->update($list);
            $sql = get_last_sql();

            // Quickly validate the query. (Bob should have been filtered out and not appear.)
            $this->assertEquals('UPDATE `funky_table` SET `name` = CASE `example_id` WHEN  4 THEN "Alice" ELSE `name` END WHERE `example_id` IN (4);', $sql);

            // Dirty state should be false
            $this->assertFalse($alice->isDirty(), 'Alice should not be dirty after update()');
            $this->assertFalse($bob->isDirty(), 'Bob should not be dirty after update()');
        }

        public function testRemove_withEmptyList()
        {
            $obj = new SampleCollection(Example::className(), null, 'funky_table', 'funky_id');
            $result = $obj->remove(array());
            $this->assertNull($result, 'An empty list should return null on remove');
        }

        /**
         * @expectedException \InvalidArgumentException
         */
        public function testRemove_ThrowsExceptionOnInvalidListItems() {
            $obj = new SampleCollection(Example::className(), null, 'funky_table', 'funky_id');
            $obj->remove(array(false));
        }


        public function testRemove()
        {
            // Create alice and make her dirty by fixing her name
            $alice = new Example('Ali');
            $alice->setPropertyValue('example_id', 4);

            $list = array($alice, 5, null);
            $obj = new SampleCollection(Example::className(), null, 'funky_table');

            // Call method and retrieve query
            $obj->remove($list);
            $sql = get_last_sql();

            // Quickly validate the query. (Bob should have been filtered out and not appear.)
            $this->assertEquals('DELETE FROM `funky_table` WHERE `example_id` IN (4, 5);', $sql);
        }

        public function testCount()
        {
            // todo: implement test case?
        }
    }
}