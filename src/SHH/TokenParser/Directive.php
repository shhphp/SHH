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
 * Directive TokenParser
 */
class Directive extends TokenParser
{
	const TYPE = DIRECTIVE_TYPE;
	public $respectIndent = true;
	
	/**
	 * Parse token.
	 *
	 * @param 	Parser 				$parser 	a Parser instance
	 *
	 * @return 	Node
	 *
	 * @throws 	ParserError		if token is at the beginning of the line
	 */
	public function parse(\SHH\Parser &$parser)
	{ 		
		if( $parser->prevIs(array(new TokenParser\Indent, new TokenParser\EOL)) ){
			return( $this->getDirectives($parser) );
		} else {
			new \SHH\ParseError( sprintf("syntax error, unexpected '%s'", $this->tok ), $this );
		}
	}

	/**
	 * Get directives.
	 *
	 * @param 	Parser 				$parser 	a Parser instance
	 *
	 * @return 	Node
	 *
	 * @throws 	ParserError		if directive doesn't exist
	 */
	protected function getDirectives(\SHH\Parser &$parser)
	{
		$pragma = $parser->expect( array(new TokenParser\Identifier, new TokenParser\Directive) )->tok;
		$error = false;

		if( $parser->is(new TokenParser\Directive) ){
			$pragma = $parser->expect( new TokenParser\Identifier )->tok ;
	
			if( $parser->directive->exist( $pragma ) ){
				$this->directiveArgs($parser, $pragma);
				$parser->directive->remove($pragma);
			} else {
				$error = true;
			}
		} else {
			if( $parser->directive->exist( $pragma ) ){
				$nodes = $this->directiveArgs($parser, $pragma);
				$parser->directive->set( $pragma );
				return( $nodes );
			} else {
				$error = true;
			}
		}
		if($error){
			$parser->capture( array(new TokenParser\EOL, new TokenParser\Directive) );
			new \SHH\ParseError( sprintf("directive '%s' doesn't exist", $pragma ), $this );
		}
	}

	/**
	 * Set directive arguments.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 * @param 	string 	$pragma 	the name of the directive
	 * @param 	bool 		$mode 		true if direcitve gets set, false if unset
	 *
	 * @return 	Node
	 */
	protected function directiveArgs(\SHH\Parser &$parser, $pragma)
	{
		if( is_callable($parser->directives[$pragma]) ){
			$arg = $parser->capture( array(new TokenParser\EOL, new TokenParser\Directive) );
			$parser->p--;
			return( $parser->directives[$pragma]($parser, \SHH\Defaults::strToArray($arg), $this) );
		} 
	}
}