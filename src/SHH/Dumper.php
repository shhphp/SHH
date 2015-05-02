<?php
namespace SHH;

/*
 * This file is part of SHH.
 * (c) 2015 Dominique Schmitz <info@domizai.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * SHH Dumper.
 */
class Dumper
{
	protected static $tablevel  = 0;
	protected static $isContent = 0;
	protected static $isChild = 0;
	protected static $parser;

	protected static $selfclosing = array(
		'area', 	 'base', 		'br', 		'col', 
		'command', 'embed', 	'hr', 		'img', 
		'input', 	 'keygen', 	'link', 	'meta', 
		'param', 	 'source', 	'track',	'wbr'
	);

	/**
	 * Dump an array of nodes.
	 *
	 * @param 	array 	$nodes 	an array of Node instances
	 *
	 * @return 	string 	the output string
	 */
	public static function dumpNodes($nodes){
		foreach((array)$nodes as $node) {
			$html .= self::build($node);
		}
		return($html);
	}

	/**
	 * Generate a output string.
	 *
	 * @param 	Parser 	$parser 	a Parser instance
	 *
	 * @return 	string 	the output string
	 */
	public static function dump(\SHH\Parser &$parser)
	{
		self::$parser =& $parser;

		if(self::$parser->directive->get(DOCTYPE) !== false && self::getRootElement($parser->nodes)->name == 'html'){
			self::$parser->directive->set(DOCTYPE, self::$parser->doctypes['html']);
		}

		if( $doctypeOption = self::$parser->directive->get(DOCTYPE) ){
			$doctype = '<!DOCTYPE '.$doctypeOption[0].'>'.self::getEOL();
		}

		if( $xmlOptions = self::$parser->directive->get(XML) ){
			if($xmlOptions['standalone'])	$xmlStandalone  = ' standalone="yes"';
			if($xmlOptions['encoding']) 	$encoding 			= ' encoding="'.$xmlOptions['encoding'].'"';	
			if($xmlOptions['version']) 		$xmlVersion 		= $xmlOptions['version'];
			$xml = '<?xml version="'.$xmlVersion.'"'.$encoding.$xmlStandalone.' ?>'.self::getEOL();
		}

		foreach(self::$parser->nodes as $node) {
			$html .= self::build($node);
		}

		return( $xml.$doctype.$html );
	}

	/**
	 * Get the root node.
	 *
	 * @param 	array 			$nodes 	an array of Node instances
	 *
	 * @return 	Node|bool 	the root element, false if more than just one root element exists
	 */
	protected static function getRootElement($nodes)
	{
		$rootNodes = 0;
		foreach($nodes as $node) {
			if($node instanceof Node\Element){
				if(++$rootNodes > 1){
					return false;
				}
			}
		}
		return $nodes[0];
	}

	/**
	 * Create a string from a node
	 *
	 * @param 	Node 	$node 	a Node instance
	 */
	protected static function build(Node $node)
	{
		
		if($node instanceof Node\Content){
			return $node->value;
		}

		if($node instanceof Node\Element){
			$tag = $node->name;
			$finalContent = null;

			$attr = array();
			self::$tablevel++;

			$singeline 		= false;
			$multiline 		= false;
			$hascontent 	= false;
			$haselement 	= false;

			foreach($node->children as $child) {
				if($child instanceof Node\Content){
					$hascontent = true;
				}
				if($child instanceof Node\Element){
					$haselement = true;
					self::$isChild++;
				}
			}

			foreach($node->children as $child) {
				if($child instanceof Node\Attribute){
					if( $child->name === 'class' && array_key_exists($child->name, $attr) ){
						$attr[$child->name] = $attr[$child->name].' '.$child->value;
					} else {
						$attr[$child->name] = $child->value;
					}

				} else if($child instanceof Node\Content){
					if($str = self::getMultiLineContent($child->value, $haselement) ){
						if(!$haselement){
							$multiline = true;
						} else {
							$singeline = true;
						}
					} else {
						$str = trim($child->value);
						$singeline = true;
					}
					$finalContent .= $str;					

				} else if($child instanceof Node\Element){
					if($hascontent) self::$isContent++;
					$finalContent .=  self::build( $child );
					if($hascontent) self::$isContent--;
					self::$isChild--;
				}
			}

			foreach ($attr as $n => $v){ 
				$attrStr .= ' '.$n.'="'.$v.'"'; 
			}

			self::$tablevel--;

			$end = self::getEOL();

			if( $haselement ){
				$eol = self::getEOL();
				$tab = self::getTab();
			}

			if( $multiline ){
				$eol = self::getEOL();
				$tab = self::getEOL().self::getTab();
			}

			if( $singeline || ($haselement && $singeline) ){
				$eol = $tab = null;
			}

			if( self::$isChild ){
				$ind = self::getTab();
				$end = self::getEOL();
			}

			if( self::$isContent ){
				$eol = $tab = $ind = $end = null;
			}


			if( $singeline ) $finalContent = trim($finalContent);
			
			if( is_array($tag) ){
				return $ind.$tag[0].$eol.$finalContent.$tab.$tag[1].$end;
			} else {
				$tag = preg_replace("/[!\"#$%&'()*+,\/;<=>?@[\]\\^`{|}~]+/", null, $tag);
			}

			if( in_array($tag, self::$selfclosing) ){ # && !$finalContent
				return $ind.'<'.$tag.$attrStr.' />'.$end;
			} else {
				return $ind.'<'.$tag.$attrStr.'>'.$eol.$finalContent.$tab.'</'.$tag.'>'.$end;
			}
		}
	}
	
	protected static function getMultiLineContent($in, $haselement){
		$c = explode("\n" , $in );
		if( sizeof($c) > 1 ){
			$s = sizeof($c);
			if($haselement){
				for ($i=0; $i < $s; $i++){
					$str .= $c[$i];
					if($i < $s-1) $str .= ' ';
				}
			} else {
				for ($i=0; $i < $s; $i++){
					$str .= self::getTab().$c[$i];
					if($i < $s-1) $str .= self::getEOL();
				}
			}
			return $str;
		}
	}

	/**
	 * Get the tab string
	 *
	 * @return 	string 	the tab string
	 */
	protected static function getTab()
	{
		if( \SHH\Defaults::$PRETTY ){
			$tab = null;
			for($i=0; $i < self::$tablevel; $i++){
				$tab.=\SHH\Defaults::$TAB;
			}
			return $tab; 
		}
	}

	/**
	 * Get the End Of Line symbol
	 *
	 * @return 	const 	the End Of Line string
	 */
	protected static function getEOL()
	{
		if( \SHH\Defaults::$PRETTY ){
			return PHP_EOL;
		}
	}
}