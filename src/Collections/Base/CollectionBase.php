<?php

namespace CrazyFactory\Data\Collections\Base;

use CrazyFactory\Core\Interfaces\IModel;
use CrazyFactory\Core\Interfaces\ISerializer;

use CrazyFactory\Core\Interfaces\ICollection;
use CrazyFactory\Utils\Arrays;

abstract class CollectionBase implements ICollection
{

    /**
     * @param IModel[] $list
     *
     * @param bool $dirtyState The preferred dirty state to filter for
     * @return IModel[] dirty list
     */
    protected static function filterModelsByDirtyState($list, $dirtyState = true)
    {
        return array_filter($list, function (IModel $item) use ($dirtyState) {
            return $item->isDirty() == $dirtyState;
        });
    }

    protected $_modelClass;
    protected $_modelPrimaryKey = null;
    protected $_serializer;

    /**
     * CollectionBase constructor.
     *
     * @param string|IModel $modelClass
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

	/**
     * Serializes models and maps them into a list (or in a pk=>data dictionary)
     *
     * @param IModel[] $list
     * @param bool $dirtyOnly
     * @param bool $asDictionary
     * @param bool $removePrimaryKey
     * @param bool $skipValidation
     *
     * @return array|null
     * @throws \OutOfRangeException
     */
    protected function serializeModels($list, $dirtyOnly = false, $asDictionary = false, $removePrimaryKey = false, $skipValidation = false) {

        // Validate if requested
        if (!$skipValidation && !Arrays::hasOnlyElementsOfClass($list, IModel::class, false)) {
            throw new \InvalidArgumentException('list contains invalid elements');
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
        $result = array();
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
                    throw new \OutOfRangeException('Missing primary key to create dictionary entry');
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
     * Restores serialized models and maps them to a list (or as a pk=>data dictionary)
     *
     * @param IModel[] $list
     * @param bool $asDictionary
     * @param bool $skipValidation
     *
     * @return array|null
     * @throws \OutOfRangeException
     */
    protected function restoreModels($list, $asDictionary = false, $skipValidation = false) {

        // Validate
        if ($list === null) {
            return null;
        }
        if (empty($list)) {
            return array();
        }

        // Restore data if required
        if ($this->_serializer) {
            $list = $this->_serializer->restoreEach($list);
        }

        // Convert
        $result = array();
        foreach ($list as $data) {

            /**
             * @var IModel $model
             */
            $model = new $this->_modelClass();
            if ($skipValidation) {
                $model->isValidatedOnChange(false);
            }

            // Add data to model
            $model->applyData($data);

            // Clean model and activate validation
            $model->resetDirtyState();
            $model->resetInvalidationState();
            $model->isValidatedOnChange(true);

            // Group result as key=>value dictionary...
            if ($asDictionary) {
                $result[$data[$this->_modelPrimaryKey]] = $model;
            }
            else {
                $result[] = $model;
            }
        }

        return $result;
    }
}