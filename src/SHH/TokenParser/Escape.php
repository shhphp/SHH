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
 * Escape TokenParser
 */
class Escape extends \SHH\TokenParser
{
	const TYPE = ESCAPE_TYPE;
	
	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance 
	 */
	public function parse(\SHH\Parser &$parser){}
}