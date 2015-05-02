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
 * Tail TokenParser
 */
class Tail extends \SHH\TokenParser
{
	const TYPE = TAIL_TYPE;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		
	 */
	public function parse(\SHH\Parser &$parser)
	{
		return( $parser->parseToks( function(&$parser, $self){
			if( $parser->is(new \SHH\TokenParser\GroupClose) && $self->group == $parser->group){
				return true;
			}
		}));
	}
}