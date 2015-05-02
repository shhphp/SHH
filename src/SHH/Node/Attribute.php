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
 * Attribute Node
 */
class Attribute implements \SHH\Node
{
	public $name;
	public $value;

	/**
	 * Constructor.
	 *
	 * @param 	string 	$name 	the name of the attribute
	 * @param 	string 	$value 	the value of the attribute
   */
	public function __construct($name = null, $value = null)
	{
		$this->name  = $name;
		$this->value = $value;
	}
}