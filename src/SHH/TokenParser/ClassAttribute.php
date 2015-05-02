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
 * ClassAttribute TokenParser
 */
class ClassAttribute extends TokenParser
{
	const TYPE = ATTRIBUTE_NAME_TYPE;
	public $respectIndent = true;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		an Element or Attribute Node 
	 */
	public function parse(\SHH\Parser &$parser)
	{
		if( $parser->prevIs(array(new TokenParser\EOL, new TokenParser\GroupOpen, new TokenParser\GroupClose, new TokenParser\Tail)) ){
			$parser->injectToken( new TokenParser\Identifier($parser->defaultElement, $this->line, $this->indent) );
			return $parser->parseCurrent();
		}
	
		if( $token = $parser->expect( array(new TokenParser\PhpShorthand, new TokenParser\Identifier, new TokenParser\SingleQuote, new TokenParser\DoubleQuote) ) ){			
			if( $parser->is( new TokenParser\Identifier ) ){
				return new \SHH\Node\Attribute( 'class', $token->tok );
			} else {
				$n = $parser->parseCurrent();
				return new \SHH\Node\Attribute( 'class', $n->value );
			}
		}
	}
}