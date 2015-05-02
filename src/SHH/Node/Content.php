<?php
namespace SHH\Node;
/*
 * This file is part of SHH.
 * (c) 2015 Dominique Schmitz <info@domizai.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Content Node
 */
class Content implements \SHH\Node
{
	public $value;

	/**
	 * Constructor.
	 *
	 * @param 	string 	$value 	the value
   */
	public function __construct($value = null)
	{
		$this->value = $value;
	}
}