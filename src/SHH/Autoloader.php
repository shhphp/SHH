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
 * Autoloads SHH classes.
 */
class Autoloader
{ 
  /**
   * Register Autoloader.
   */
  public static function register()
  {
    spl_autoload_register(array(__CLASS__, 'autoload'));
  }

   /**
    * Handles autoloading of classes.
    *
    * @param  string  $class  a class name.
    */
	public static function autoload($class)
  {
    if(0 !== strpos($class, 'SHH\\')){
    	return;
    }

    $file = dirname(__FILE__).preg_replace( '/((SHH)?\\\)/', DIRECTORY_SEPARATOR, $class).'.php';
    
    if( is_file($file) ){
    	require_once $file;
    }
	}
}
Autoloader::register();