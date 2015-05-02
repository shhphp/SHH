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
 * MultiLineComment TokenParser
 */
class MultiLineComment extends \SHH\TokenParser
{
	const TYPE = MULTILINE_COMMENT_TYPE;
	public $contentType = true;
	
	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 */
	public function parse(\SHH\Parser &$parser)
	{
		$parser->capture( $this );
	}
}