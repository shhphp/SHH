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
 * HTMLComment TokenParser
 */
class HTMLComment extends \SHH\TokenParser
{
	const TYPE = HTML_COMMENT_TYPE;
	public $contentType = true;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		a Content Node 
	 */
	public function parse(\SHH\Parser &$parser)
	{		
		if( $comment = $parser->capture($this) ){
			return new \SHH\Node\Element( array("<!--", "-->") ,
				array( new \SHH\Node\Content( $comment ))
			);
		}
	}
}