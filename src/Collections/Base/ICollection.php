<?php
/**
 * Created by PhpStorm.
 * User: Wolf
 * Date: 7/8/2016
 * Time: 13:00
 */

namespace CrazyFactory\Core\Collections\Base;

use CrazyFactory\Core\Models\Base\IModel;

interface ICollection {

	/**
	 * @param IModel|IModel[] $listOrModel
	 *
	 * @return mixed The primary key of the last added object.
	 */
	function add($listOrModel);

	/**
	 * @param IModel|IModel[] $listOrModel
	 *
	 * @return int Amount of actually updated items.
	 */
	function update($listOrModel);

	/**
	 * @param IModel|IModel[] $listOrModel
	 *
	 * @return mixed Amount of actually removed items.
	 */
	function remove($listOrModel);


	/**
	 * @return int Number of matching items.
	 */
	function count();
}