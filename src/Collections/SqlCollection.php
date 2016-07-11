<?php
/**
 * Created by PhpStorm.
 * User: Wolf
 * Date: 7/8/2016
 * Time: 12:55
 */

namespace CrazyFactory\Core\Collections;

use CrazyFactory\Core\Collections\Base\CollectionBase;
use CrazyFactory\Core\Models\Base\IModel;
use CrazyFactory\Core\Models\Base\ModelBase;
use CrazyFactory\Core\Serializers\Base\ISerializer;

class SqlCollection extends CollectionBase
{
    public static function determineTableFromClassName($className)
    {

        // No string, no result!
        if ($className === null) {
            return null;
        }
        if (!is_string($className)) {
            throw new \InvalidArgumentException('not a string');
        }

        // Initialise trimmed and opt out if empty
        $tableName = trim($className);

        // Strip out namespace
        if (strpos($tableName, '\\') != 0) {
            $tableName = array_reverse(explode('\\', $tableName))[0];
        }

        // Is a valid (and code style compliant) class name?
        $pattern = "/^[A-Z]([a-zA-Z0-9_]+[a-zA-Z0-9])*$/";
        if (!$tableName || !preg_match($pattern, $tableName)) {
            throw new \InvalidArgumentException('class name is invalid');
        }

        // These suffices will be removed from the end of the string.
        // if named properly even a Collection for Collections is ok,
        // because it should be named 'Collections'.
        $suffices = ['Collection', 'Set', 'Table'];

        // Try to strip out first suffix found
        foreach ($suffices as $suffix) {
            if ($tableName !== $suffix && stripos(strrev($tableName), strrev($suffix)) === 0) {
                $tableName = substr($tableName, 0, strlen($tableName) - strlen($suffix));
                break;
            }
        }

        // Append an 's' if missing
        if (strpos(strrev($tableName), 's') !== 0) {
            $tableName .= 's';
        }

        return $tableName;
    }

    protected $_tablePrimaryKey;
    protected $_tableName;

    /**
     * CollectionBase constructor.
     *
     * @param IModel $modelClass
     *
     * @param ISerializer $serializer
     * @param string $tableName
     * @param string $tablePrimaryKey
     *
     * @throws \Exception
     */
    public function __construct($modelClass, ISerializer $serializer = null, $tableName = null, $tablePrimaryKey = null)
    {
        parent::__construct($modelClass, $serializer);


        // Fail if no primary key was set for the model
        if (!$this->_modelPrimaryKey) {
            throw new \Exception('Model primary key is empty');
        }

        // Try to determine table name by calling class
        if ($tableName === null) {
            // In SqlCollection we use the Model Class name
            // In an inherited collection we use the collections name
            $tableName = get_called_class() == SqlCollection::class
                ? SqlCollection::determineTableFromClassName((string) $modelClass)
                : SqlCollection::determineTableFromClassName(get_called_class());
        }

        // Try determine table primary key
        if ($tablePrimaryKey === null) {
            $tablePrimaryKey = $this->_modelPrimaryKey;
        }

        // Constructed values
        $pattern = "/^[a-zA-Z_][a-zA-Z0-9_]*$/";

        if (!$tableName || !preg_match($pattern, $tableName)) {
            throw new \Exception('Table name is not valid');
        }

        if (!$tablePrimaryKey || !preg_match($pattern, $tablePrimaryKey)) {
            throw new \Exception('Table primary key is not valid');
        }

        // Accept args
        $this->_tableName = $tableName;
        $this->_tablePrimaryKey = $tablePrimaryKey;
    }

    /**
     * @param IModel[]|IModel $listOrModel
     *
     * @return array|bool|resource
     */
    public function add($listOrModel)
    {
        // Ensure it's an array
        $list = is_array($listOrModel) ? $listOrModel : [$listOrModel];

        // Convert models to dictionary of dirty data (with PK=>DirtyData)
        $data_list = $this->serializeModels($list);
        if (!$data_list) {
            return null;
        }

        $sql = $this->buildInsertQuery($data_list, $this->_tableName, $this->_tablePrimaryKey);

        // Fire Query
        $last_insert_id = df_query($sql);

        // Update model primary keys and mark them as clean
        $current_id = $last_insert_id;
        foreach ($list as $item) {
            $item->setPropertyValue($this->_modelPrimaryKey, $current_id);
            $item->resetDirtyState();
            $current_id++;
        }

        return $last_insert_id;
    }

    /**
     * @param IModel[]|IModel $listOrModel
     *
     * @return int|null
     */
    public function update($listOrModel)
    {
        // Ensure it's an array
        $list = is_array($listOrModel) ? $listOrModel : [$listOrModel];

        // Convert models to dictionary of dirty data (with PK=>DirtyData)
        $dirty_data_dic = $this->serializeModels($list, true, true);
        if (!$dirty_data_dic) {
            return null;
        }

        // Create the query
        $sql = $this->buildUpdateQuery($dirty_data_dic, $this->_tableName, $this->_tablePrimaryKey);

        // Fire!
        $query_result = $sql
            ? df_query($sql)
            : null;

        // Reset dirty state if query was successful
        if ($query_result) {
            foreach ($list as $item) {
                $item->resetDirtyState();
            }
        }

        return $query_result;
    }

    /**
     * @param int|IModel|mixed[] $listOrValue
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    public function remove($listOrValue)
    {
        // Wrap
        $list = is_array($listOrValue) ? $listOrValue : [$listOrValue];

        // Gather all primary keys
        $ids = [];
        foreach ($list as $item) {
            // Integer Id
            if (is_int($item) && $item > 0) {
                $ids[] = $item;
            }
            // Model
            else if ($item instanceof $this->_modelClass) {
                $modelId = $item->getPropertyValue($this->_modelPrimaryKey);
                if (!(is_int($modelId) && $modelId > 0)) {
                    throw new \InvalidArgumentException();
                }
                $ids[] = $modelId;
            }
            else if ($item !== null) {
                throw new \InvalidArgumentException();
            }
        }

        // return nothing on empty.
        if (empty($ids)) {
            return null;
        }

        // remove duplicate elements from list
        $ids = array_unique($ids);

        // Build and Fire/Return Query
        $sql = $this->buildRemoveQuery($this->_tableName, $this->_tablePrimaryKey, $ids);
        return df_query($sql);
    }

    //	public function all($limit = 100, $offset = 0) {
    //
    //	}
    //
    //	public function getById($listOrId) {
    //
    //	}

    private function buildRemoveQuery($tableName, $tableKey, $matchValues) {
        // todo: implement
    }


    /**
     * @param array[] $pk_to_data_dic
     * @param string $table_name
     * @param string $table_primary_key
     *
     * @return null|string Returns a query string or null if none of the models needs updating.
     * @throws \Exception
     */
    private function buildUpdateQuery($pk_to_data_dic, $table_name, $table_primary_key) // todo: move to php-cf-core
    {
        // Get all columns that will be updated
        $columns = $this->getUniqueKeysFromElements($pk_to_data_dic);
        if (!$columns) {
            return null;
        }

        // Don't allow changing of primary keys!
        if (in_array($table_primary_key, $columns)) {
            throw new \Exception('Tried changing a primary key');
        }

        // Get a list of all primary keys we need to touch
        $primary_keys = array_keys($pk_to_data_dic);

        // Build sql string
        $sql = 'UPDATE ' . $table_name . ' SET ';
        $sql .= implode(', ', $this->buildUpdateQueryCases($columns, $table_primary_key, $pk_to_data_dic));
        $sql .= ' WHERE ' . $table_primary_key . ' IN (' . implode(', ', $primary_keys) . ')' . "\n";

        return $sql;
    }

    /**
     * @param string[] $columns
     * @param string   $table_primary_key
     * @param array[]  $pk_to_data_dic
     *
     * @return array $cases
     */
    private function buildUpdateQueryCases($columns, $table_primary_key, $pk_to_data_dic)
    {
        $cases = [];

        foreach ($columns as $column) {

            $case = $column . ' = CASE' . "\n";

            foreach ($pk_to_data_dic as $primary_key => $data) {
                if (empty($data) || !key_exists($column, $data)) { // todo: show dave difference between isset and key_exists.
                    continue;
                }

                $case .= ' WHEN ' . $table_primary_key . ' = ' . $primary_key . ' THEN ' . df_sqlval($data[$column]) . "\n";

            }

            $case .= ' ELSE ' . $column . "\n";
            $case .= ' END' . "\n";
            $cases[] = $case;
        }

        return $cases;
    }

    /**
     * @param array[] $data_list
     * @param string $table_name
     * @param string $table_primary_key
     *
     * @return int
     * @throws \Exception
     */
    protected function buildInsertQuery($data_list, $table_name, $table_primary_key)
    {
        // Return null on empty values
        if (!$data_list) {
            return null;
        }

        // Require valid table name
        if (!$table_name || !is_string($table_name)) {
            throw new \Exception("Table name is required");
        }

        // Require valid primary key
        if (!$table_primary_key || !is_string($table_primary_key)) {
            throw new \Exception("Table primary key is required");
        }

        // Get all affected columns
        $columns = $this->getUniqueKeysFromElements($data_list);

        // Unset primary key (it probably exists, but should only contain null values anyway)
        unset($columns[$table_primary_key]);


        // Build INSERT INTO ... VALUES clause
        $sql = 'INSERT INTO `' . $table_name . '` (' . implode(', ', $columns) . ')';

        $data_list_values = [];
        foreach ($data_list as $data) {
            $data_values = [];
            foreach ($columns as $column) {
                if (!key_exists($column, $data)) {
                    throw new \Exception("missing data value for column '".$column."'");
                }
                $data_values[] = df_sqlval($data[$column]);
            }
            // Create inserted data set string
            $data_list_values[] = ' (' . implode(', ', $data_values) . ')';
        }

        // Append/Concat all data set strings
        $sql .= ' VALUES' . implode(', ', $data_list_values);
        return $sql;
    }

    function count()
    {
        // TODO: Implement count() method.
    }
}