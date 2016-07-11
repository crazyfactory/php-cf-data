<?php
/**
 * @license see LICENSE
 */

namespace CrazyFactory\Core\Exceptions;

use \Exception as Exception;

class PropertyOutOfRangeException extends Exception
{
	protected $_invalidProperties;

	/**
	 * PropertyValidationException constructor.
	 *
	 * @param array          $invalidKeyValues
	 * @param string|null    $message
	 * @param int|null       $code
	 * @param Exception|null $previous
	 */
	public function __construct($invalidKeyValues = null, $message = null, $code = null, Exception $previous = null)
	{
		if ($message === null) {
			if (count($invalidKeyValues) > 0) {
				$message = "Validation failed for: " . (implode(", ", array_keys($invalidKeyValues)));
			}
			else {
				$message = "Validation failed.";
			}
		}

		parent::__construct($message, $code, $previous);

		$this->_invalidProperties = $invalidKeyValues;
	}

	/**
	 * @return array
	 */
	public function getInvalidProperties()
	{
		return $this->_invalidProperties;
	}
}