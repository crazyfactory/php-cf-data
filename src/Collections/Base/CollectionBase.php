<?php

namespace CrazyFactory\Core\Collections\Base;

use CrazyFactory\Core\Exceptions\PropertyOutOfRangeException;
use CrazyFactory\Core\Models\Base\IModel;
use CrazyFactory\Core\Models\Base\ModelBase;
use CrazyFactory\Core\Serializers\Base\ISerializer;

abstract class CollectionBase implements ICollection
{
    /**
     * @param IModel[]|IModel $listOrModel
     *
     * @return IModel[]
     */
    protected static function wrapInArray($listOrModel) // todo: remove?
    {
        return is_array($listOrModel)
            ? $listOrModel
            : [$listOrModel];
    }

    /**
     * @param array[] $list
     *
     * @return string[]
     */
    protected static function getUniqueKeysFromElements($list) // todo: move to php-cf-utils
    {
        $keys = [];
        foreach ($list as $item) {
            $keys += array_keys($item);
        }

        $keys = array_unique($keys);

        sort($keys, SORT_STRING);

        return $keys;
    }


    /**
     * @param IModel[] $list
     *
     * @param bool $dirtyState The preferred dirty state to filter for
     * @return \CrazyFactory\Core\Models\Base\IModel[] dirty list
     */
    protected static function filterModelsByDirtyState($list, $dirtyState = true)
    {
        return array_filter($list, function (IModel $item) use ($dirtyState) {
            return $item->isDirty() == $dirtyState;
        });
    }

    protected $_modelClass;
    protected $_modelPrimaryKey = null;

    protected $_tableColumnMap = [];

    protected $_serializer;

    /**
     * CollectionBase constructor.
     *
     * @param $modelClass
     * @param ISerializer $serializer Handles conversion between model and data properties
     * @throws \Exception
     */
    public function __construct($modelClass, ISerializer $serializer = null)
    {
        if (!is_subclass_of($modelClass, IModel::class)) {
            throw new \Exception('modelClass is missing IModel interface');
        }

        $this->_modelClass = $modelClass;

        if ($serializer !== null) {
            $this->_serializer = $serializer;
        }

        // If a primary key is set, require a certain format
        if (null !== $modelClass::PRIMARY_KEY) {
            $pk = $modelClass::PRIMARY_KEY;
            $pattern = "/^[a-zA-Z_][a-zA-Z0-9_]*$/";

            if (!$pk || !is_string($pk) || !preg_match($pattern, $pk)) {
                throw new \Exception('Model PRIMARY_KEY is invalid.');
            }

            if ($pk && is_string($pk)) {
                $this->_modelPrimaryKey = $pk;
            }
        }
    }

    // Map Models into [id: string|int] => <data>
	/**
     * @param IModel[] $list
     * @param bool $dirtyOnly
     * @param bool $asDictionary
     * @param bool $removePrimaryKey
     * @param bool $skipValidation
     *
     * @return array|null
     * @throws PropertyOutOfRangeException
     */
    protected function serializeModels($list, $dirtyOnly = false, $asDictionary = false, $removePrimaryKey = false, $skipValidation = false) {

        // Validate if requested
        if (!$skipValidation) {
            $this->validateModelTypes($list);
        }

        // Filter out non-dirty if requested
        if ($dirtyOnly) {
            $list = self::filterModelsByDirtyState($list);
        }

        // If list is empty now, return null
        if (empty($list)) {
            return null;
        }

        // Convert
        $result = [];
        foreach ($list as $item) {
            $item_data = $item->extractData($dirtyOnly);

            // Remove primary key if requested
            if ($removePrimaryKey) {
                unset($item_data[$this->_modelPrimaryKey]);
            }

            // Use Serializer on data
            if ($this->_serializer) {
                $item_data = $this->_serializer->serialize($item_data);
            }

            // Group result as key=>value dictionary...
            if ($asDictionary) {
                $item_key = $item->getPropertyValue($this->_modelPrimaryKey);
                if (!$item_key) {
                    throw new PropertyOutOfRangeException('Missing primary key to create dictionary entry');
                }
                $result[$item_key] = $item_data;
            }
            // ... or as list
            else {
                $result[] = $item_data;
            }
        }

        return $result;
    }


    /**
     * @param  IModel[] $list
     *
     * @return IModel[]
     * @throws \Exception
     */
    protected function validateModelTypes($list)
    {
        foreach ($list as $item) {
            if (!($item instanceof $this->_modelClass)) {
                throw new \Exception('Invalid Model in list');
            }
        }

        return $list;
    }

    /**
     * @param IModel[] $list
     *
     * @return int[]
     */
    private function getAllPrimaryKeys($list)
    {
        return array_map(function ($item) {
            /**
             * @var ModelBase $item
             */
            return $item->getPropertyValue($this->_modelPrimaryKey);
        }, $list);
    }
}