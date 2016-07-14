<?php
/**
 * Created by PhpStorm.
 * User: Wolf
 * Date: 7/12/2016
 * Time: 16:35
 */

namespace CrazyFactory\Data\Test;

use CrazyFactory\Core\Interfaces\IModel;
use CrazyFactory\Data\Collections\Base\CollectionBase;
use CrazyFactory\Data\Models\Base\ModelBase;

class Collection extends CollectionBase
{

	/**
	 * @param IModel|IModel[] $listOrModel
	 *
	 * @return mixed The primary key of the last added object.
	 */
	function add($listOrModel)
	{
		// empty, don't test!
	}

	/**
	 * @param IModel|IModel[] $listOrModel
	 *
	 * @return int Amount of actually updated items.
	 */
	function update($listOrModel)
	{
		// empty, don't test!
	}

	/**
	 * @param IModel|IModel[] $listOrModel
	 *
	 * @return mixed Amount of actually removed items.
	 */
	function remove($listOrModel)
	{
		// empty, don't test!
	}

	/**
	 * @return int Number of matching items.
	 */
	function count()
	{
		// empty, don't test!
	}

	function restoreModels($list, $asDictionary = false, $skipValidation = false)
	{
		return parent::restoreModels($list, $asDictionary, $skipValidation);
	}

	function serializeModels($list, $dirtyOnly = false, $asDictionary = false, $removePrimaryKey = false, $skipValidation = false)
	{
		return parent::serializeModels($list, $dirtyOnly, $asDictionary, $removePrimaryKey, $skipValidation);
	}
}

class Model extends ModelBase
{

}


class CollectionBaseTest extends \PHPUnit_Framework_TestCase
{
	public function test__construct()
	{
		// Test if the class is constructed/compiled correctly.
		$obj = new Collection(Model::class);
	}

	public function testRestoreModels()
	{

		// TODO: Implement test case

	}

	public function testSerializeModels()
	{
		// TODO: Implement test case
	}
}