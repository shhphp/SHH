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
 * Reference TokenParser
 */
class Reference extends TokenParser
{
	const TYPE = REFERENCE_TYPE;
	public $respectIndent = true;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 				$parser 	a Parser instance
	 *
	 * @return 	Node 					
	 *
	 * @throws 	ParserError		if reference is not defined
	 */
	public function parse(\SHH\Parser &$parser)
	{
		if( $anchor = $parser->expect(new TokenParser\Identifier)->tok ){
			if($ref = $parser->refs[$anchor]){
				$parser->removeToken($parser->p-1, $parser->p);

				foreach ($ref[1] as $r){
					$r->indent += ($this->indent - $r->indent);
					$r->line += ($this->line - $r->line);
				}

				$parser->p++;
				$parser->injectToken($ref[1]);
				$parser->p += sizeof($ref[1]);

				return $ref[0];
			} else {
				new \SHH\ParseError(sprintf("Reference '%s' is not defined", $anchor), $parser->toks[$parser->p] );
			}
		}
	}
}