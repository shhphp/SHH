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
 * Anchor TokenParser
 */
class Anchor extends TokenParser
{
	const TYPE = ANCHOR_TYPE;
	public $respectIndent = true;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node  	the next parsed TokenParser in the stream
	 */
	public function parse(\SHH\Parser &$parser)
	{
		$p = $parser->p;
		if( $anchor = $parser->expect(new TokenParser\Identifier)->tok ){
			$parser->removeToken($p, $parser->p++);
			
			while( $parser->current()->captureIgnore ){
				$parser->p++;
			}

			$from = $parser->p;
			$n = $parser->parseCurrent();
			$to = $parser->p;
			
			for($toks = array(), $c = 0, $i=$from; $i < $to; $i++, $c++){
				$toks[$c] = $parser->toks[$i];
			}

			$parser->refs[$anchor] = array($n, $toks);
			return $n;
		} 
	}
}