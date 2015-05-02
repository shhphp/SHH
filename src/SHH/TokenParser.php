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
 * Base class for all token parsers.
 */
abstract class TokenParser
{
	public $tok; 
	public $line;
	public $indent;
	public $group;
	public $scope;

	/**
   * Constructor.
   *
   * @param 	string 	$tok 			the token string
   * @param 	int 		$line 		the line number of the token
   * @param 	int	 		$indent 	the indent value of the token
   */
	public function __construct($tok = null, $line = null, $indent = null)
	{ 
		$this->tok    = $tok; 
		$this->line   = $line;
		$this->indent = $indent; 
	}

	/**
	 * Parse token and eventually return a Node instance or an array of Node instances.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 */
	abstract public function parse(\SHH\Parser &$parser);
}