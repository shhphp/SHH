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
 * PhpShorthand TokenParser
 */
class PhpShorthand extends \SHH\TokenParser
{
	const TYPE = PHP_SHORTHAND_TYPE;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		an Content Node
	 */
	public function parse(\SHH\Parser &$parser)
	{
		if( $token = $parser->expect( new TokenParser\Identifier ) ){		
			$func = trim( $parser->capture( array(new TokenParser\Whitespace, new TokenParser\EOL), false, null, false, true ) ); 
			return new \SHH\Node\Content("<?php echo $".$token->tok.$func."; ?>");
		}
	}
}
