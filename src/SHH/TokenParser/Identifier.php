<?php
namespace SHH\TokenParser;
use SHH\TokenParser;
use SHH\Node;
use SHH\Parser;

/*
 * This file is part of SHH.
 * (c) 2015 Dominique Schmitz <info@domizai.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Identifier TokenParser
 */
class Identifier extends TokenParser
{
	const TYPE = IDENTIFIER_TYPE;
	public $respectIndent = true;
	protected static $autotagSelf = false;

	/**
	 * Parse token.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	Node 		an Element, Attribute or Content Node 
	 */
	public function parse(Parser &$parser)
	{
		# This order is crucial
		if( $node = $this->addCode($parser) ) 	   return $node;
		if( $node = $this->addAutotag($parser) ) 	 return $node;
		if( $node = $this->addAttribute($parser) ) return $node; 
		if( $node = $this->addContent($parser) )   return $node;
		if( $node = $this->addElement($parser) )   return $node; 
	}

	protected function addAutotag(Parser &$parser)
	{
		if( !$parser->directive->get(AUTOTAG) ){
			return false;
		}

		if( self::$autotagSelf ){
			self::$autotagSelf = false;
			return false;
		}

		if( array_key_exists($this->tok, $parser->autotags ) ){
			if( $parser->parent && $parser->autotags[$this->tok][2] ){
				if( !in_array(end($parser->parent)->tok, \SHH\Defaults::toArray($parser->autotags[$this->tok][2])) ){
					return false;
				}
			}

			$p = $parser->p;
			$newName = $parser->autotags[$this->tok][0];

			$toks = array();
			$toks[] = new TokenParser\Identifier($newName, $this->line, $this->indent);

			if( is_callable($parser->autotags[$this->tok][1]) ){
				if( $parser->nextIs(new TokenParser\Assign, null, true) ){
					$arg = $parser->parseCurrent();
				}

				foreach($parser->autotags[$this->tok][1]($arg->value) as $attr => $value) {
					if($value){
						$toks[] = new TokenParser\Attribute('@', $this->line, $this->indent);
						$toks[] = new TokenParser\Identifier($attr, $this->line, $this->indent);
						$toks[] = new TokenParser\Assign('=', $this->line, $this->indent);
						$toks[] = new TokenParser\Identifier($value, $this->line, $this->indent);
					}
				}
			}

			$parser->removeToken($p, $parser->p);
			$parser->p = $p;
			$parser->injectToken($toks);

			if( $newName == $this->tok) self::$autotagSelf = true;
			return( $parser->parseCurrent() );
		}
	}

	protected function addAttribute(Parser &$parser)
	{
		if( $parser->nextIs(new TokenParser\Assign) ){
			if( $parser->prevIs(array(new TokenParser\EOL, new TokenParser\GroupOpen, new TokenParser\GroupClose, new TokenParser\Tail)) ){
				$parser->injectToken(array(
					new TokenParser\Identifier($parser->defaultElement, $this->line, $this->indent), 
					new TokenParser\Whitespace(' ', $this->line, $this->indent))
				);
				return( $parser->parseCurrent() );
			}

			$parser->expect(new TokenParser\Assign);
			return new Node\Attribute( $this->tok, $parser->parseCurrent()->value );
		}
	}

	protected function addCode(Parser &$parser)
	{
		if( $parser->nextIs(new TokenParser\Code) ){
			$parser->expect(new TokenParser\Code);

			$parser->scope++;
			$code = $parser->parseCurrent();
			$parser->scope--;

			if(array_key_exists($this->tok, $parser->filter) ){
				# parseCurrent
				if( !(is_callable($parser->filter[$this->tok][2]) && $content = $parser->filter[$this->tok][2]( $code->value )) ){
					$content = $code->value;
				} 

				if($parser->filter[$this->tok][0]){
					return new Node\Element( array($parser->filter[$this->tok][0], $parser->filter[$this->tok][1]), array( new Node\Content($content) ));
				} else {
					return new Node\Content( $parser->format($content) );
				}
			} else {
				return new Node\Element( $this->tok, array(new Node\Content( $code->value )));
			}
		} 
	}

	protected function addContent(Parser &$parser)
	{
		if( $parser->prevIs( array(new TokenParser\Php, new TokenParser\Identifier, new TokenParser\Escape, new TokenParser\SingleQuote, new TokenParser\DoubleQuote) )){
			$string = $parser->capture( array(new TokenParser\PhpShorthand, new TokenParser\EOL, new TokenParser\GroupClose ));
			$parser->p--;
			return new Node\Content( $this->tok.$string );
		}
	}

	protected function addElement(Parser &$parser)
	{
		if( $parser->directive->get(HTML) && !in_array($this->tok, $parser->htmlElements) ){
			$string = $parser->capture( array(new TokenParser\PhpShorthand, new TokenParser\EOL, new TokenParser\GroupClose ));
			$parser->p--;
			return new Node\Content( trim($this->tok.$string) );
		}

		if( preg_match('/^</', $this->tok) ){
			$string = $parser->capture( array(new TokenParser\EOL));
			return new Node\Content( $this->tok.$string );
		}

		$parser->scope++;
		$parser->parent[] =& $this;
		
		# Scope/group counting is crucial
		$nodes = $parser->parseToks( function( &$parser, &$self ){
			if( $parser->current()->respectIndent
				&& $parser->current()->indent <= $self->indent
				&& $parser->current()->line > $self->line
			){
				$parser->scope--;
				return true;
			}

			// div[eol](...
			if( ($parser->current() instanceof TokenParser\GroupOpen || $parser->current() instanceof TokenParser\Anchor )
				&& $parser->current()->indent > $self->indent
				&& $parser->current()->line > $self->line 
			){
				$parser->scope++;
			}

			if( $parser->current() instanceof TokenParser\GroupClose){
				if( $self->group+1 == $parser->group && $self->scope == $parser->scope ) return true;
				if( $self->scope+1 == $parser->scope && $self->group == $parser->group){
					$parser->scope--;
					return true;
				}
			}
		});

		$parser->scope = max(0, $parser->scope);
		array_pop($parser->parent);

		return new Node\Element( $this->tok, $nodes );	
	}
}