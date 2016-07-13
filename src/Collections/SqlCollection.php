<?php
/**
 * Created by PhpStorm.
 * User: Wolf
 * Date: 7/8/2016
 * Time: 12:55
 */

namespace CrazyFactory\Data\Collections;

use CrazyFactory\Core\Interfaces\IModel;
use CrazyFactory\Core\Interfaces\ISerializer;
use CrazyFactory\Utils\SqlDeleteQuery;
use CrazyFactory\Utils\SqlInsertQuery;
use CrazyFactory\Utils\SqlSchemes;
use CrazyFactory\Data\Collections\Base\CollectionBase;
use CrazyFactory\Utils\SqlUpdateQuery;

class SqlCollection extends CollectionBase
{
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
            $tableName = (get_called_class() instanceof SqlCollection)
                ? SqlSchemes::determineTableName((string) $modelClass)
                : SqlSchemes::determineTableName(get_called_class());
        }

        // Try determine table primary key
        if ($tablePrimaryKey === null) {
            $tablePrimaryKey = $this->_modelPrimaryKey;
        }

        // Validate final values
        if (SqlSchemes::isValidTableName($tableName)) {
            throw new \Exception('Table name is not valid');
        }

        if (SqlSchemes::isValidColumnName($tablePrimaryKey)) {
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
        $list = is_array($listOrModel) ? $listOrModel : array($listOrModel);

        // Convert models to serialized list
        $data_list = $this->serializeModels($list);
        if (!$data_list) {
            return null;
        }

        // Build query (omit the primary key)
        $sql = SqlInsertQuery::buildBulk($this->_tableName, $data_list, [$this->_tablePrimaryKey]);

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
        $list = is_array($listOrModel) ? $listOrModel : array($listOrModel);

        // Convert models to dictionary of dirty data (with PK=>DirtyData)
        $dirty_data_dic = $this->serializeModels($list, true, true);
        if (!$dirty_data_dic) {
            return null;
        }

        // Create the query (data is a dictionary, so we treat it as a dictionary)
        $sql = SqlUpdateQuery::buildBulk($this->_tableName, $this->_tablePrimaryKey, $dirty_data_dic, true);

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
        $list = is_array($listOrValue) ? $listOrValue : array($listOrValue);

        // Gather all primary keys
        $ids = array();
        foreach ($list as $item) {
            // Integer Id
            if (is_int($item) && $item > 0) {
                $ids[] = $item;
            }
            // Model
            else if ($item instanceof $this->_modelClass) {
                $ids[] = $item->getPropertyValue($this->_modelPrimaryKey);
            }
            else if ($item !== null) {
                throw new \InvalidArgumentException();
            }
        }

        // remove duplicate and falsy elements from list
        $ids = array_unique(array_filter($ids));

        // Construct SQL
        $sql = SqlDeleteQuery::buildBulk($this->_tableName, $this->_tablePrimaryKey, $ids);

        // Fire if truthy, or return null
        return $sql
            ? df_query($sql)
            : null;
    }

    //	public function all($limit = 100, $offset = 0) {
    //
    //	}
    //
    //	public function getById($listOrId) {
    //
    //	}

    function count()
    {
        // TODO: Implement count() method.
    }
}