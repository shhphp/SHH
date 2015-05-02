<?php
namespace SHH\TokenParser;

/*
 * This file is part of SHH.
 * (c) 2015 Dominique Schmitz <info@domizai.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Indent TokenParser
 */
class Indent extends \SHH\TokenParser
{
	const TYPE = INDENT_TYPE;
	public $captureIgnore = true;
	
	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 */
	public function parse(\SHH\Parser &$parser){}
}