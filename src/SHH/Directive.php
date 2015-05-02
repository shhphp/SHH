<?php
namespace SHH;

/*
 * This file is part of SHH.
 * (c) 2015 Dominique Schmitz <info@domizai.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * SHH Directive.
 */ 
class Directive
{
	protected $directives = array();

	/**
	 * Add a directive.
	 *
	 * @param 	string 	$flag 	the name of the directive
	 */ 
	public function add($flag)
	{
		if(!$this->directives[$flag = mb_strtolower($flag)]){
			$this->directives[$flag] = array();
		}
	}

	/**
	 * Check if a directive exists.
	 *
	 * @param 	string 	$flag 	the name of the directive
	 */ 
	public function exist($flag)
	{
		return isset($this->directives[mb_strtolower($flag)]);
	}

	/**
	 * Check if a directive is set.
	 *
	 * @param 	string 		$flag 	the name of the directive
	 *
	 * @return 	mixed 		the values of the directive
	 *										returns false if the direcitve is removed
	 */ 
	public function get($flag)
	{
		return( $this->directives[mb_strtolower($flag)] );
	}

	/**
	 * Set a directive.
	 *
	 * @param 	string 	$flag 		the name of the directive
	 * @param 	mixed 	$values 	optional values to add to the directive
	 */
	public function set($flag, $values = true)
	{
		$this->add($flag = mb_strtolower($flag));
		$this->directives[$flag] = array_merge($this->directives[$flag], (array)$values);
	}

	/**
	 * Remove a directive.
	 *
	 * @param 	string 	$flag 		the name of the directive
	 * @param 	mixed 	$values 	optional values to add to the directive
	 */
	public function remove($flag)
	{
		$this->directives[mb_strtolower($flag)] = false;
	}

	/**
	 * Remove all directives.
	 */
	public function removeAll()
	{
		foreach($this->directives as &$directive){
			$directive = false;
		}
	}
}