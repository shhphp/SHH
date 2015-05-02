<?php
namespace SHH\TokenParser;
use SHH\TokenParser;

/*
 * This file is part of SHH.
 * (c) 2015 Dominique Schmitz <info@domizai.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Assign TokenParser
 */
class Assign extends TokenParser
{
	const TYPE = ASSIGNMENT_TYPE;
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
		if( $token = $parser->expect( array(new TokenParser\PhpShorthand, new TokenParser\Escape, new TokenParser\Identifier, new TokenParser\SingleQuote, new TokenParser\DoubleQuote) ) ){			
			if( $parser->is( new TokenParser\Identifier ) ){
				return new \SHH\Node\Content( $token->tok );
			} else {
				$n =  $parser->parseCurrent();
				return new \SHH\Node\Content( $n->value );
			}
		}
	}
}