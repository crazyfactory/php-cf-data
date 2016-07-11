<?php
/**
 * Created by PhpStorm.
 * User: Wolf
 * Date: 7/8/2016
 * Time: 14:26
 */


namespace CrazyFactory\Core\Models\Base;

use CrazyFactory\Core\Exceptions\PropertyNotFoundException;
use CrazyFactory\Core\Exceptions\PropertyOutOfRangeException;

interface IModel {

	const PRIMARY_KEY = "id";

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws PropertyNotFoundException
	 * @return mixed
	 */
	function setPropertyValue($name, $value);

	/**
	 * @param string $name
	 *
	 * @throws PropertyNotFoundException
	 * @return mixed
	 */
	function getPropertyValue($name);

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws PropertyNotFoundException
	 * @throws PropertyOutOfRangeException
	 * @return mixed
	 */
	function isValidPropertyValue($name, $value);

	/**
	 * @param bool $dirtyOnly
	 *
	 * @return array
	 */
	function extractData($dirtyOnly);

	/**
	 * @param array $data
	 *
	 * @throws PropertyOutOfRangeException
	 * @throws PropertyNotFoundException
	 * @return void
	 */
	function applyData($data);

	/**
	 * @return bool
	 */
	function isDirty();

	/**
	 * @return bool
	 */
	function isValidated();

	/**
	 * @return void
	 */
	function resetDirtyState();

	/**
	 * @return void
	 */
	function resetInvalidationState();

	/**
	 * @throws PropertyOutOfRangeException
	 * @return void
	 */
	function validate();

	/**
	 * @return bool
	 */
	function tryValidate();
}