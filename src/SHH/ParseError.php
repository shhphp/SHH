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
 * Exception thrown when an error occurs during parsing.
 */
class ParseError
{
	/**
   * Constructor. Stops the script if an error is thrown and \SHH\Defaults::$ERROR_REPORTING is set to true.
   *
   * @param 	string 				$message 	the error message to display
   * @param 	TokenParser 	$line 		the TokeParser instance which threw the error
   */
	public function __construct($message, \SHH\TokenParser $tokenParser)
	{
		if( \SHH\Defaults::$ERROR_REPORTING ){
			$file = \SHH\Compiler::getFile();
			die("<br><b>SHH Parse error</b>: {$message} ".($file ?"in <b>{$file}</b> ":null)."on line <b>{$tokenParser->line}</b>.");
		} 
	}

	/**
	 * Get the token of a TokenParser instance or its type if token is a space character.
	 *
	 * @param 	TokenParser 	$tok 	a TokenParser instance
	 */
	public static function tokenType(\SHH\TokenParser $tok)
	{
		return (trim($tok->tok))? $tok->tok : $tok::TYPE;
	}
}