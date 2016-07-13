<?php
/**
 * @license see LICENSE
 */

namespace CrazyFactory\Data\Test;

use CrazyFactory\Data\Models\Base\ModelBase;

// inherits from abstract class
class SimpleModel extends ModelBase
{
    function __construct($name = null, $age = null, $location = null)
    {
        $this->initProperties([
            'name' => $name,              // Has NO set/get/isValid
            'age' => $age,
            'location' => $location
        ]);
    }
}

class ValidatingModel extends ModelBase
{
    function __construct()
    {
        $this->initProperties([
            'has_no_validator' => null,
            'must_be_bool' => null
        ]);
    }

    static function isValidMust_be_bool($value)
    {
        return is_bool($value);
    }
}

class ModelBaseTest extends \PHPUnit_Framework_TestCase
{
    const VALUES_OF_DIFFERENT_TYPES = array(
        null, false, 0, 15, '', 'alice', 'MoreComplex_and-STRANGE+with#123'
    );

    public function testIsValidPropertyValue_WithoutValidator()
    {
        $obj = new ValidatingModel();
        $this->assertTrue($obj->isValidPropertyValue('has_no_validator', 123),
            'should return true if no validator exists.');
    }

    public function testValidatePropertyValue_WithValidator()
    {
        $obj = new ValidatingModel();

        $this->assertTrue($obj->isValidPropertyValue('must_be_bool', true),
            'should return true for valid value.');

        $this->assertFalse($obj->isValidPropertyValue('must_be_bool', 124),
            'should return false for invalid value');
    }

    /**
     * @expectedException \CrazyFactory\Core\Exceptions\PropertyNotFoundException
     */
    public function testIsValidPropertyValue_ThrowsNotFoundException()
    {
        $obj = new ValidatingModel();
        $obj->isValidPropertyValue('does_not_exist', 5);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetPropertyValue_PropertyOutOfRanceException()
    {
        $obj = new ValidatingModel();
        $obj->setPropertyValue('must_be_bool', 5);
    }

    /**
     * @expectedException \CrazyFactory\Core\Exceptions\PropertyNotFoundException
     */
    public function testExceptionThrownOnInvalidPropertySet()
    {
        $obj = new SimpleModel();
        $obj->setPropertyValue('this_property_does_not_exist', null);
    }

    /**
     * @expectedException \CrazyFactory\Core\Exceptions\PropertyNotFoundException
     */
    public function testExceptionThrownOnInvalidPropertyGet()
    {
        $obj = new SimpleModel();
        $obj->getPropertyValue('this_property_does_not_exist');
    }

    /**
     * @covers \CrazyFactory\Core\Models\Base\ModelBase getPropertyValue
     */
    public function testSetAndGetPropertyValue()
    {
        foreach (self::VALUES_OF_DIFFERENT_TYPES as $value) {
            $obj = new SimpleModel();

            // Set value and compare return value
            $returned_value = $obj->setPropertyValue('name', $value);
            $this->assertEquals($value, $returned_value, 'setProperty() should have returned the set value');

            // Get value and compare as well
            $returned_get_value = $obj->getPropertyValue('name');
            $this->assertEquals($value, $returned_get_value, 'getProperty() should have returned the previously set value');
        }
    }

    public function testIsValidated()
    {
        // Create new model and ensure instant validation is activated
        $obj = new ValidatingModel();
        $this->assertTrue($obj->isValidatedOnChange(), 'new instance should be validatingOnChange');

        // Check if model is marked valid
        $this->assertTrue($obj->isValidated(), 'new instance should be validated');
    }

    public function testIsValidated_WithValidator()
    {
        // Create new model and ensure instant validation is activated
        $obj = new ValidatingModel();

        // Change a value with validator
        $obj->setPropertyValue('must_be_bool', true);
        $this->assertTrue($obj->isValidated());

        // Deactivate Validation
        $obj->isValidatedOnChange(false);

        // Change the value again
        $obj->setPropertyValue('must_be_bool', false);
        $this->assertFalse($obj->isValidated());
    }

    public function testIsValidated_WithoutValidator()
    {
        // Create new model
        $obj = new ValidatingModel();

        // Change a value without validator
        $obj->setPropertyValue('has_no_validator', 12345);
        $this->assertTrue($obj->isValidated());

        // Deactivate Validation
        $obj->isValidatedOnChange(false);

        // Change a property (both should trigger the change, so we just choose one
        $obj->setPropertyValue('has_no_validator', 54321);
        $this->assertFalse($obj->isValidated());
    }

    public function testResetInvalidationState()
    {
        // Create new model and ensure instant validation is activated
        $obj = new ValidatingModel();

        // Deactivate Validation
        $obj->isValidatedOnChange(false);

        // Change a property
        $obj->setPropertyValue('has_no_validator', 54321);

        // Ensure state is invalid
        $this->assertFalse($obj->isValidated());

        // Reset state and check :)
        $obj->resetInvalidationState();
        $this->assertTrue($obj->isValidated());
    }

    public function testIsDirty()
    {
        $obj = new SimpleModel('alice');

        $this->assertFalse($obj->isDirty(), 'is dirty by default');

        // Set value as before
        $obj->setPropertyValue('name', 'alice');

        $this->assertFalse($obj->isDirty(),
            'is dirty after setting an equal value');

        $obj->setPropertyValue('name', 'bob');

        $this->assertTrue($obj->isDirty(), 'is not dirty after value has been changed');
    }

    public function testExtractData()
    {
        // Create new instance and check if it returns the initial values
        $obj = new SimpleModel('alice', 13);
        $data = $obj->extractData();
        $this->assertEquals(
            array('name' => 'alice', 'age' => 13, 'location' => null),
            $data);

        // Get Dirty Data and assume it's an empty erray
        $this->assertEquals(
            array(),
            $obj->extractData(true),
            'extract dirty data should return an empty array after instantiation.');

        // Change Data
        $obj->setPropertyValue('location', 'Wonderland');

        // Compare Data again
        $data = $obj->extractData();
        $this->assertEquals(
            array('name' => 'alice', 'age' => 13, 'location' => 'Wonderland'),
            $data,
            'property change should have changed result.');

        // Compare Dirty Data again
        $dirty_data = $obj->extractData(true);
        $this->assertEquals(
            array('location' => 'Wonderland'),
            $dirty_data,
            'extract dirty data should indicated priorly changed values.');
    }

    public function testApplyData()
    {
        // Define sample data sets which will be applied one after another to the same model.
        $sets = array(
            array(
                'name' => 'Alice',
                'location' => 'Wonderland'
            ),
            array(/*empty*/),
            array(
                'age' => 21
            ),
        );
        $obj = new SimpleModel();

        // Apply all sets in a sequence and check if getPropertyValue() returns the applied values correctly.
        foreach ($sets as $data) {
            $obj->applyData($data);

            foreach ($data as $key => $value) {
                $this->assertEquals($value, $obj->getPropertyValue($key));
            }
        }
    }

    public function testValidate() {
        $obj = new ValidatingModel();
        $obj->isValidatedOnChange(false);

        $obj->setPropertyValue('must_be_bool', true);

        $obj->validate();
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testValidate_ThrowsException() {
        $obj = new ValidatingModel();
        $obj->isValidatedOnChange(false);

        $obj->setPropertyValue('must_be_bool', 15);
        $obj->validate();
    }
}






