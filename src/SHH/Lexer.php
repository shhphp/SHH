<?php
namespace SHH;
use SHH\TokenParser;

/*
 * This file is part of SHH.
 * (c) 2015 Dominique Schmitz <info@domizai.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * SHH Lexer.
 */
class Lexer
{
	protected static $lex = array();
	protected static $line;
	protected static $isContent = false;
	protected static $isComment = false;

	/**
	 * Tokenize current input.
	 *
   * @param  string 	$input 	input string
   *
   * @return array 		an array of TokenParser instances
   */
	public static function tokenize( $input )
	{
		self::initTokens();
		self::removeSingleLineComments($input);
		return self::getTokens($input);
	}

	/**
	 * Screener. Removes single line comments from the input.
	 *
	 * @param 	string 	$input 	input string
   */
	protected static function removeSingleLineComments(&$input)
	{
		$input = preg_replace('/^\s*-(?!-).*/m', null, $input);
	}

	/**
	 * Initialize Tokens.
   */
	protected static function initTokens()
	{
		if(!self::$lex){	
			self::$lex = array(
				array(\SHH\Defaults::$TAB, new TokenParser\Indent(\SHH\Defaults::$TAB)),
				array('-{3}', new TokenParser\HTMLComment('---')),
				array('-{2}', new TokenParser\MultiLineComment('--')),
				array('\s', 	new TokenParser\Whitespace(' ')),
				array('#', 		new TokenParser\Id('#')),
				array('\.', 	new TokenParser\ClassAttribute('.')),
				array('@', 		new TokenParser\Attribute('@')),
				array('=', 		new TokenParser\Assign('=')),
				array('"', 		new TokenParser\DoubleQuote('"')),
				array('\'', 	new TokenParser\SingleQuote("'")),
				array('%', 		new TokenParser\Code('%')),
				array('\(', 	new TokenParser\GroupOpen('(')),
				array('\)', 	new TokenParser\GroupClose(')')),
				array('>', 		new TokenParser\Tail('>')),
				array('\\\\', new TokenParser\Escape('\\')),
				array('!', 		new TokenParser\Directive('!')),
				array('\?', 	new TokenParser\Php('?')),
				array('\$', 	new TokenParser\PhpShorthand('$')), 
				array('&', 		new TokenParser\Anchor('&')),
				array('\*', 	new TokenParser\Reference('*'))
			); 
		}
	}

	/**
	 * Tokenize lexer input.
	 *
   * @param  	string 	$input 	input string
	 *
	 * @return 	array 	an array of TokenParser instances
   */
	protected static function getTokens($input)
	{
		self::$line = 0;
		self::$isContent = false;
		self::$isComment = false;
		$toks = array();
		$toks[] = new TokenParser\EOL(PHP_EOL, self::$line, 0);

		foreach(explode("\n", preg_replace(array('/\r\n|\r/','/\t/'), array("\n", \SHH\Defaults::$TAB), $input)) as $line){
			self::$line++; 
			$toks = array_merge($toks, self::getTokensOnCurrentLine( $line ));
		}

		return $toks;
	}

	/**
	 * Tokenize current line. 
	 *
	 * @param 	string 	$line 	string to be lexed	
	 *
	 * @return 	array 	an array of TokenParser instances
	 */
	protected static function getTokensOnCurrentLine( &$line )
	{
		$indent = 0;
		$toks = array();

		$patt = "";
		$s = sizeof(self::$lex);
		for ($i=0; $i < $s; $i++) { 
			$patt .= self::$lex[$i][0].(($i<$s-1)?'|':null);
		}
		$regex = "/($patt)|((?!$patt)\S)+/";

		preg_match_all($regex, $line, $m);		

		$mS = sizeof($m[0]);
		$mL = sizeof(self::$lex);
		for($i=0; $i < $mS; $i++){ 
			for($j=0; $j < $mL; $j++){ 
				if( self::$lex[$j][1]->tok === $m[0][$i] ){

					# skip multi line comments
					if( self::$lex[$j][1] instanceof TokenParser\MultiLineComment && !end($toks) instanceof TokenParser\Escape){
						self::$isComment ^= true;
						if(self::$isComment){
							continue 2;
						} else {
							return array();
						}
					}

					# for performance purpose only
					if( self::$lex[$j][1]->contentType && !end($toks) instanceof TokenParser\Escape){
						if( !self::$isContent ){
							self::$isContent = $m[0][$i];

							$toks[] = clone self::$lex[$j][1];
							end($toks)->line   = self::$line;
							end($toks)->indent = $indent;
							continue 2;
						} else {
							if( $m[0][$i] === self::$isContent && !end($toks) instanceof TokenParser\Escape){
								self::$isContent = false;
							} else {
								break;
							}
						}
					}
					if( self::$isContent && !self::$lex[$j][1] instanceof TokenParser\Escape) break; 


					if( end($toks) instanceof TokenParser\Escape ){
							$toks[] = new TokenParser\Identifier($m[0][$i], self::$line, $indent); 
					} else {
						if( self::$lex[$j][1] instanceof TokenParser\Indent ){
							if( end($toks) instanceof TokenParser\Indent || !end($toks) ) $indent++;
						}
						$toks[] = clone self::$lex[$j][1]; 
					}

					if( self::$lex[$j][1] instanceof TokenParser\Indent ){ 
						end($toks)->tok  = \SHH\Defaults::$TAB;
					} else {
						end($toks)->tok  = $m[0][$i];
					}

					end($toks)->line   = self::$line;
					end($toks)->indent = $indent;
					continue 2;
				} 
			}

			if(self::$isComment) return $toks;

			if( end($toks) instanceof TokenParser\Identifier ){
				end($toks)->tok .= $m[0][$i];
			} else if( $m[0][$i] || end($toks) instanceof TokenParser\Escape){
				$toks[] = new TokenParser\Identifier( $m[0][$i], self::$line, $indent ); 
			}
		}

		$toks[] = new TokenParser\EOL(PHP_EOL, self::$line, $indent);
		return $toks;
	}
}