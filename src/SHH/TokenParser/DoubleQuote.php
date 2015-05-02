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
 * DoubleQuote TokenParser
 */
class DoubleQuote extends \SHH\TokenParser
{
	const TYPE = DOUBLE_QUOTE_TYPE;
	public $contentType = true;
	public $respectIndent = true;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		a Content Node 
	 */
	public function parse(\SHH\Parser &$parser)
	{
		if( $string = $parser->capture( $this, false, null, true ) ){
			return new \SHH\Node\Content( $parser->format($string) );
		}		
	}
}