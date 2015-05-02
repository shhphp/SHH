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
 * SHH default settings. Also provides helper functions.
 */ 
class Defaults
{
	public static $TAB = '  ';
	public static $ERROR_REPORTING = false;
	public static $PRETTY = true;
	public static $EXTENSIONS = array('shh');

	/**
   * Set the indentation level.
   *
   * @param 	int 	$int 	the tab size
   */
	public static function setTabSize($int)
	{
		for(self::$TAB=null, $i=0; self::$TAB.=' ', $i<intval($int)-1; $i++);
	}

	/**
   * Break a string into an array.
   *
   * @param 	string 	$str 	a string 
   *
   * @return 	array 	an array of strings
   */
	public static function strToArray($str){
		$args = array();
		foreach(explode(" ", trim(preg_replace('/\s+/', ' ', trim($str)))) as $m){
			if( strlen($m = trim($m)) > 0 ){
				$args[] = $m;
			}
		}
		return $args;
	}

	/**
   * Create an array
   *
   * @return 	array
   */
	public static function toArray(&$array)
	{
		return $array = (is_array($array)? $array : array($array) );
	}
}