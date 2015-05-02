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
 * Element Node
 */
class Element implements \SHH\Node
{
	public $name;
	public $children = array();

	/**
	 * Constructor.
	 *
	 * @param 	string 	$value 			the name of the element
	 * @param 	array 	$children 	ann array of Node instances
   */
	public function __construct($name = null, $children = null)
	{
		$this->name   = $name;
		$this->children = $children;
	}
}