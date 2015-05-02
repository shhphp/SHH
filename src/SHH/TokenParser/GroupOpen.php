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
 * GroupOpen TokenParser
 */
class GroupOpen extends \SHH\TokenParser
{
	const TYPE = GROUP_OPEN_TYPE;
	public $respectIndent = true;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		
	 */
	public function parse(\SHH\Parser &$parser)
	{
		$parser->group++;
		$nodes = $parser->parseToks( function( &$parser, &$self ){
			if( $parser->current() instanceof \SHH\TokenParser\GroupClose ){
				if( $self->group+1 == $parser->group ){
					$parser->scope--; 
					return true;
				}
			}
		});

		if( !$parser->toks[$parser->p] ){
			new \SHH\ParseError( sprintf("syntax error, unexpected %s, expecting %s", \SHH\ParseError::tokenType( end($parser->toks) ), ')' ), $this );
		}
		return $nodes;
	}
}