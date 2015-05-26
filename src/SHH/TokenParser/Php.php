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
 * Php TokenParser
 */
class Php extends \SHH\TokenParser
{
	const TYPE = PHP_TAG_TYPE;
	public $contentType = true;
	public $respectIndent = true;
	
	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		an Content Node
	 */
	public function parse(\SHH\Parser &$parser)
	{
		if($code = $parser->capture($this) ){
			return new \SHH\Node\Element( array("<?php ", " ?>"),
				array( new \SHH\Node\Content( $parser->format($code) ) )
			);
		}
	}
}
