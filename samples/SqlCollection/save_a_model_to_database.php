<?php

// Fake SQL Query function
function df_query($sql) { var_dump($sql); return 999; }
function df_sqlval($value) { return '"' . addslashes($value) . '"'; }

// Non Composer style requirements
require_once('../../src/Exceptions/PropertyNotFoundException.php');
require_once('../../src/Exceptions/PropertyOutOfRangeException.php');

require_once('../../src/Collections/Base/ICollection.php');
require_once('../../src/Collections/Base/CollectionBase.php');
require_once('../../src/Collections/SqlCollection.php');

require_once('../../src/Models/Base/IModel.php');
require_once('../../src/Models/Base/ModelBase.php');
require_once('../../src/Models/IdModel.php');

require_once('../../src/Serializers/Base/ISerializer.php');
require_once('../../src/Serializers/Base/SerializerBase.php');
require_once('../../src/Serializers/DataToDataSerializer.php');


// Create a simple User Model
use CrazyFactory\Core\Models\IdModel;

class User extends IdModel {
	function __construct($id = null) {
		parent::__construct($id);

		$this->initProperties([
			'age' => null,
			'name' => null
		]);
	}
}

// --- Create an SqlCollection
// Here we're using an SqlCollection class directly
// Alternatively we can inherit from it. If no Table Name is supplied
use \CrazyFactory\Data\Collections\SqlCollection;
$collection = new SqlCollection(User::className());

// --- Load the model
// Get a user model from somewhere
// (we fake loading a model here, but normally you should get one via a collection!)
$user = new User();
$user->isValidatedOnChange(false);
$user->applyData([
	'name' => 'Alice',
    'id' => 1,
    'age' => 13
]);
$user->resetInvalidationState();
$user->resetDirtyState();
$user->isValidatedOnChange(true);

// --- Work with the model
// The Model is validated and clean at this point, the dirty data is an empty array
var_dump($user->isDirty()); // => false
var_dump($user->extractData(true)); // => []

// Change a model value. This marks the model as dirty.
$user->setPropertyValue('age', 14);
var_dump($user->isDirty()); // => true
var_dump($user->extractData(true)); // => ['age' => 14]

// Pass the model into the collection for updating
// (the fake df_query() will var_dump() the constructed Sql statement)
$collection->update($user);

// If successful, the model will be marked as clean again
var_dump($user->isDirty()); // => false

