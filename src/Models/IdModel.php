<?php
/**
 * @license see LICENSE
 */

namespace CrazyFactory\Core\Models;

use CrazyFactory\Data\Models\Base\ModelBase;

/**
 * Class IdModel
 * A basic model with an integer Id property to be used for various purposes.
 */
abstract class IdModel extends ModelBase
{
	const PRIMARY_KEY = "id";

	/**
	 * @param int|null $id
	 */
	public function __construct($id = null)
	{
		$this->initProperties(array(
			'id' => $id,
		));
	}

	/**
	 * @param $value int
	 */
	public function setId($value)
	{
		$this->setPropertyValue('id', $value);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		// return $this->_properties['id']; // Quicker, skips some checks, maybe use it in generated files
		return $this->getPropertyValue('id');
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public static function isValidId($id)
	{
		return $id === null || (is_int($id) && $id > 0);
	}
}
