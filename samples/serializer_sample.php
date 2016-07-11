<?php
/**
 * Created by PhpStorm.
 * User: Wolf
 * Date: 7/11/2016
 * Time: 08:07
 */

// Non Composer style requirements
require_once('../src/Serializers/Base/ISerializer.php');
require_once('../src/Serializers/Base/SerializerBase.php');
require_once('../src/Serializers/DataToDataSerializer.php');



$model_data = [
	'customers_id' => 5,
	'name' => 'alice',
	'age' => 15,
    'location' => 'Wonderland',
    'shoes' => new DateTime()
];

class CustomSerializer extends \CrazyFactory\Core\Serializers\Base\SerializerBase {


	/**
	 * @param $input
	 *
	 * @return mixed
	 */
	function serialize($input)
	{
		// TODO: Implement serialize() method.
	}

	/**
	 * @param $input
	 *
	 * @return mixed
	 */
	function restore($input)
	{
		// TODO: Implement restore() method.
}}

$serializer = new \CrazyFactory\Core\Serializers\DataToDataSerializer([
	'shoes' => false, 
	'customers_id' => 'customer_id'], [],
	function($output, $input) {
		var_dump($input);
		var_dump($output);

		$output['date'] = $input['shoes']->getOffset();

		return $output;
	});

$serialized_data = $serializer->serialize($model_data);

var_dump($model_data);

var_dump($serialized_data);

