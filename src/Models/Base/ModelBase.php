<?php

namespace CrazyFactory\Data\Models\Base;

use OutOfRangeException;
use CrazyFactory\Core\Exceptions\PropertyNotFoundException;
use CrazyFactory\Core\Interfaces\IModel;

abstract class ModelBase implements IModel
{
    protected $_properties = array();
    protected $_dirtyProperties = array();
    protected $_isValidatedOnChange = true;
    protected $_isValidated = true;

    /**
     * @return bool
     */
    public function isValidated()
    {
        return $this->_isValidated;
    }

    public function resetInvalidationState()
    {
        $this->_isValidated = true;
    }

    public function setPropertyValue($name, $value)
    {
        // Die on unknown property
        if (!array_key_exists($name, $this->_properties)) {
            throw new PropertyNotFoundException("Unknown property '" . $name . "'");
        }

        // Value changed?
        if ($this->_properties[$name] !== $value) {

            // Opt out on invalid value
            if ($this->_isValidatedOnChange && !$this->isValidPropertyValue($name, $value, false)) {
                throw new OutOfRangeException("'" . $name . "' can't accept value '" . $value . "'!");
            }

            // If not yet done store previous value
            $this->_dirtyProperties[$name] = true;
            $this->_properties[$name] = $value;

            // Mark as not validated?
            if (!$this->_isValidatedOnChange) {
                $this->_isValidated = false;
            }
        }

        // Return truth
        return $this->_properties[$name];
    }

    public function getPropertyValue($key)
    {
        if (!key_exists($key, $this->_properties)) {
            throw new PropertyNotFoundException("Unknown property '" . $key . "'");
        }

        return $this->_properties[$key];
    }

    public function isValidPropertyValue($key, $value, $throwPropertyNotFoundException = true)
    {
        if ($throwPropertyNotFoundException && !key_exists($key, $this->_properties)) {
            throw new PropertyNotFoundException("Unknown property '" . $key . "'");
        }

        // Target validator
        $fnName = "isValid" . ucfirst($key);

        // Check for existing validator
        if (method_exists($this, $fnName)) {
            // Validate value or throw up
            if (!call_user_func(array($this, $fnName), $value)) {
                return false;
            }
        }

        return true;
    }

    protected function initProperties($data)
    {
        $this->_properties = array_merge($this->_properties, $data);
    }

    public function isDirty()
    {
        return $this->_dirtyProperties !== null && !empty($this->_dirtyProperties);
    }

    public function resetDirtyState()
    {
        $this->_dirtyProperties = null;
    }

    public function extractData($dirtyOnly = false)
    {
        if (!$dirtyOnly) {
            return $this->_properties;
        }
        else if (!$this->isDirty()) {
            return array();
        }
        else {

            $dirtyArray = array();

            foreach ($this->_dirtyProperties as $dirtyName => $dirtyValue) { // value is always true...
                $dirtyArray[$dirtyName] = $this->_properties[$dirtyName];
            }

            return $dirtyArray;
        }
    }


    public function applyData($data)
    {
        $invalidProperties = array();

        // todo: use array_diff_keys?
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $this->_properties)) {
                $invalidProperties[] = $key;
            }
        }

        if (!empty($invalidProperties)) {
            throw new PropertyNotFoundException("Unknown properties: " . implode(", ", $invalidProperties));
        }

        if ($this->_isValidatedOnChange) {
            $invalidValues = array();
            foreach ($data as $key => $value) {
                if (!$this->isValidPropertyValue($key, $value, false)) {
                    $invalidValues[$key] = $value;
                }
            }
            if (!empty($invalidValues)) {
                throw new OutOfRangeException($invalidValues);
            }
        }

        // Update Data after all checks have passed!
        $hasChangedValues = false;
        foreach ($data as $key => $value) {
            if ($this->_properties[$key] !== $value) {
                $hasChangedValues = true;
                $this->_properties[$key] = $value;
                $this->_dirtyProperties[$key] = true;
            }
        }

        // Mark as unvalidated?
        if ($hasChangedValues && !$this->_isValidatedOnChange) {
            $this->_isValidated = false;
        }
    }

    /**
     * @return void
     * @throws \OutOfRangeException;
     */
    public function validate()
    {
        // Return true if already validated
        if ($this->_isValidated) {
            return;
        }

        // Validate all properties
        $invalidProperties = array();
        foreach ($this->_properties as $key => $value) {
            if (!$this->isValidPropertyValue($key, $value)) {
                $invalidProperties[] = $key;
            }
        }

        // Throw exception on error
        if (!empty($invalidProperties)) {
            throw new OutOfRangeException('Invalid values for properties: ' . implode(", ", $invalidProperties));
        }

        // Update state
        $this->_isValidated = true;
    }

    /**
     * @return bool
     */
    public function tryValidate()
    {
        try {
            $this->validate();
        }
        catch (OutOfRangeException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param null $value
     *
     * @return bool
     */
    public function isValidatedOnChange($value = null)
    {
        if ($value !== null) {
            $this->_isValidatedOnChange = (bool)$value;
        }

        return $this->_isValidatedOnChange;
    }
}