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
 * SHH Parser.
 */
class Parser
{
	public $directive;
	public $p;
	public $toks = array();
	public $nodes= array();
	public $filter = array();
	public $autotags = array();
	public $interpolatorss = array();
	public $refs = array();
	public $parent;
	public $data = array();
	public $group = 0;
	public $scope = 0;
	public $defaultElement = 'div';
	public $xmlStandalone = false; 
	public $encoding = false;
	public $xmlVersion = '1.0';
	public $doctype = 'html';
	public $directives = array();
	public $doctypes = array(
		'html' => 'html',
		'5' => 'html',
		'default' => 'html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"',
		'transitional' => 'html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"',
		'strict' => 'html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"',
		'frameset' => 'html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd"',
		'1.1' => 'html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"',
		'basic' => 'html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd"',
		'mobile' => 'html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd"'
	);
	public $htmlElements = array(
		'a', 				'abbr', 			'acronym', 		'address', 		'applet', 
		'area', 		'article',		'aside',			'audio',			'b',
		'base',			'basefont',		'bdi',				'bdo',				'bgsound',
		'big',			'blink',			'blockquote',	'body',				'br',
		'button',		'canvas',			'caption',		'center',			'cite',
		'code',			'col',				'colgroup',		'command',		'content',
		'data',			'datalist',		'dd',					'del',				'details',
		'dfn',			'dialog',			'dir',				'div',				'dl',
		'dt',				'element',		'em',					'embed',			'fieldset',
		'fidcaption','figure',		'font',				'footer',			'form',
		'frame',		'frameset',		'head',				'header',			'hgroup',
		'h1', 			'h2', 				'h3', 				'h4', 				'h5', 'h6',
		'hr',				'html',				'i',					'iframe',			'image',
		'img',			'input',			'ins',				'iaindex',		'kbd',
		'keygen',		'label',			'legend',			'li',					'link',
		'listing',	'main',				'map',				'mark',				'marquee',
		'menu',			'menuitem',		'meta',				'meter',			'mutlicol',
		'nav',			'nobr',				'noembed',		'noframes',		'noscript',
		'noscript',	'object',			'ol',					'optgroup',		'option',
		'output',		'p',					'param',			'picture',		'plaintext',
		'pre',			'progress',		'q',					'rp',					'rt',
		'ruby',			's',					'samp',				'script',			'section',
		'select',		'shadow',			'small',			'source',			'spacer',
		'span',			'strike',			'strong',			'style',			'sub',
		'summary',	'sup',				'table',			'tbody',			'td',
		'template',	'textarea',		'tfoot',			'th',					'thead',
		'time',			'title',			'tr',					'track',			'tt',
		'u',				'ul',					'var',				'video',			'wbr','xmp'
	);

	/**
	 * Constructor.
   */
	public function __construct()
	{ 
		$this->directive = new \SHH\Directive();
	}

	/**
	 * Add a content filter.
	 *
   * @param  string 		$key 				a regular expression
   * @param  callable 	$callback 	a method to modify the matched string
   */
	public function interpolation($regex, $callback){
		$this->interpolatorss[] = array($regex, $callback);
	}

	/**
	 * Add code tag.
	 *
   * @param  string|array $key 				the name of the filter
   * @param  string 			$open 			open tag
   * @param  string 			$close 			close tag
   * @param  callable 		$callback 	a method to modify the output string
   */
	public function filter($key, $open = null, $close = null, $callback = null)
	{
		foreach((array)$key as $k){
			$this->filter[$k] = array($open, $close, $callback);
		}
	}

	/**
	 * Add autotag. Transforms an element or an attribute into a new element.
	 *
   * @param  string|array 	$key 			name of element or attribute
   * @param  array 					$element 	new element name
   * @param  callable 			$callback attribute name and value
   * 																	should return an array in form of: array( attrName => value );
   * @param  string|array 	$parent 	name of direct parent alement allowed
   */
	public function autotag($key, $callback, $element = null, $parent = null)
	{
		foreach((array)$key as $k){
			$this->autotags[$k] = array(($element?$element:$k), $callback, $parent);
		}
	}

	/**
	 * Add a directive.
	 *
   * @param  string 				$key 				name of directive
   * @param  callable 			$callback 	a method which will be called when parsing
   */
	public function directive($key, $callback = null)
	{
		$this->directives[$key] = $callback;
		$this->directive->add($key);
	}
	/**
	 * Add tokens to the current token stream.
	 *
   * @param  TokenParser|array 	$tok 	token to be added
   * @param  integer 						$pos 	position
   */
	public function injectToken($tok, $pos = null)
	{
		array_splice( $this->toks, ($pos?$pos:$this->p), 0, \SHH\Defaults::toArray($tok) );
	}

	/**
	 * Remove tokens from the current token stream.
	 *
   * @param  integer 	$from 	start position
   * @param  integer 	$to 		end position
   */
	public function removeToken($from, $to)
	{
		array_splice( $this->toks, $from, ($to-$from)+1 );
		$this->p-=2;
	}

	/**
	 * Test TokenParser instances to another TokenParser instance.
	 *
   * @param 	TokenParser|array 	$toks 	TokenParser's to test
   * @param   TokenParser 				$test 	TokenParser to test with. 
   *																			use current token if null.
   * @return 	TokenParser|null 		
   */
	public function is($toks, $test = null)
	{ 
		$test = $test ? $test : $this->toks[$this->p];
		if( $this->includes($test, $toks) ){ 
			return $test; 
		}
	}

	/**
   * Test TokenParser instances to next TokenParser instance.
   *
   * @param 	TokenParser|array 	$expected 	Next TokenParser instances to test
   * @param  	TokenParser|array 	$ignore 		TokenParser insatnces to ignore
   *																					if null, ignore TokenParser's with variable captureIgnore set to true.
   *																					if false, none will be ignored.
   * @return 	TokenParser|null 		returns matched TokenParser instance
   */
	public function nextIs($expected, $ignore = null, $move = false, $add = 1)
	{ 
		$tp = $this->p+$add;
		if($tp < 0){
			return false;
		}

		if($ignore !== false){
			if($ignore){
				while( $this->toks[$tp] && $this->includes($this->toks[$tp], $ignore) ) $tp += $add;
			} else {
				while( $this->toks[$tp] && $this->toks[$tp]->captureIgnore ) $tp += $add; 
			}
		}

		if( $tok = $this->is($expected, $this->toks[$tp])){
			if($move){
				$this->p = $tp;
			}
			return( $tok );
		}
	}

	/**
   * Test TokenParser instances to previous TokenParser instance.
   *
   * @see 	\SHH\Parser::nextIs()
   */
	public function prevIs($expected, $ignore = null, $move = false, $add = -1)
	{ 
		return $this->nextIs($expected, $ignore, $move, $add);
	}

	/**
   * Test next TokenParser instance. Moves cursor and returns it or throws an error.
   *
   * @param  TokenParser|array 	$expected 	Expected TokenParser instance to occur
   * @param  TokenParser|array 	$ignore 		TokenParser instances to ignore
   *
   * @return TokenParser 				expected TokenParser instance
   *
   * @throws ParseError 				if next TokenParser instance is not expected
   */
	public function expect($expected, $ignore = null)
	{
		if( $node = $this->nextIs(\SHH\Defaults::toArray($expected), $ignore, true) ){
			return $node;
		}

		if(  \SHH\Defaults::$ERROR_REPORTING ){
			$s = sizeof($expected);
			for ($i=0; $i < $s; $i++){
				$expect .= (
					($expected[$i]->tok)?"'".$expected[$i]->tok."'" : $expected[$i]::TYPE) .
					(($i<sizeof($expected)-1)?(($i<sizeof($expected)-2)?", ":" or ") : null 
				);
			}
			new \SHH\ParseError(sprintf("unexpected %s, expecting %s", \SHH\ParseError::tokenType($this->toks[$this->p]), $expect ), $this->toks[$this->p] );
		}
	}

	/**
	 * Create a string from the TokenParser stream
	 *
   * @param 	TokenParser|array 	$find 		an array of TokenParser intances to match
   * @param   bool  							$with 		adds the last and matched TokenParser to the string 
   * @param   TokenParser|array  	$escape 	TokenParser instances to escape
   * @param   bool  							$suppress suppress error message
   *
   * @return 	string 							the final merged string
   *
   * @throws 	ParseError					if no TokenParser matches
   */
	public function capture($find, $with = false, $escape = null, $interpolation = false, $suppress = false)
	{
		if(!$escape) $escape = $this->current()->tok;
		$original = reset( \SHH\Defaults::toArray($find) );

		while( $this->toks[++$this->p] ){
			if( $this->includes($this->toks[$this->p], $find) ){
				if($with){
					$tok .= $this->toks[$this->p]->tok;
				}

				$match = true;
				break;
			}

			if( !$this->is(new TokenParser\Escape) || $this->toks[$this->p+1]->tok{0} !== $escape ){
				$tok .= $this->toks[$this->p]->tok; 
			}
		}

		# interpolation
		if( $interpolation ){
			foreach ($this->interpolatorss as $filter) {
				if( is_callable($filter[1]) ){
					$flags = end(explode('/', $filter[0]));
					$filter[0] = preg_replace('/^\/|\/$|\/\S+$/', null, $filter[0]);

					$tok = preg_replace_callback('/(\\\\)?(?:'.$filter[0].')/'.$flags, function($match) use (&$filter) {
						if(!$match[1]){
							if(sizeof($match)>1) {
								$match = array_slice($match, 2, sizeof($match)-1);
							}
							return($filter[1]($match));
						} else {
							return trim($match[0],'\\');
						}
					}, $tok);
				}
			}
		}

		if($match || $suppress) return $tok;
		new \SHH\ParseError(sprintf("syntax error, unexpected %s, expecting %s", \SHH\ParseError::tokenType( $this->toks[$this->p-1] ), \SHH\ParseError::tokenType($original) ), $this->toks[$this->p-1] );
		return $tok;
	}

	/**
	 * Trim and add correct indentation to string.
	 *
   * @param 	string 	$string		the string to format
   *
   * @return 	string 	the final string
   */
	public function format($string)
	{
		$c = explode("\n" , $string );

		if(sizeof($c) > 1){
			
			# remove empty lines from end
			while( $c && !strlen(trim(end($c))) ){
				unset($c[sizeof($c)-1]);
			}

			# remove emtpy lines from top
			while( $c && !strlen(trim($c[0])) ){
				$firstLineEmpty = true;
				array_splice($c, 0, 1);
			}

			# get lowest indent
			$lowest = null;
			$s = sizeof($c);
			for ($i=0; $i < $s; $i++){
				if( (!$firstLineEmpty && $i == 0) || !strlen(trim($c[$i])) ) continue; 
				preg_match("/^(?:[ \t]+)/", $c[$i], $ind);
				$len = $ind[0] ? strlen($ind[0]) : 0;
				$lowest = ($lowest!==null ? ($len < $lowest ? $len : $lowest) : $len);
			}

			# trim indents
			$s = sizeof($c);
			for ($i=0; $i < $s; $i++){
				$final .= preg_replace("/^(?: {".$lowest."})/",null, $c[$i]).PHP_EOL;
			}
			$final = trim($final);
			
			// var_dump($final);

			return $final;
		} else {
			return $string;
		}
	}

	/**
	 * Check if a TokeParser instance matches an instance of an TokenParser array.
	 *
   * @param 	TokenParser 				$expected 	the TokenParser isntance to match
   * @param   TokenParser|array  	$toks 			an array of TokenParser insatnces 
   *
   * @return 	bool 								true if match
   */
	protected function includes(TokenParser &$expected, $toks)
	{
		$s = sizeof( \SHH\Defaults::toArray($toks) );
		for($i=0; $i < $s; $i++){ 
			if( $expected instanceof $toks[$i] ){
				return true; 
			}
		}
	}

	/**
	 * Get an instance of the current TokenParser stream or its type.
	 *
	 * @param  string 		$type 		return the type of the current TokenParser instance if set to 'TYPE'
	 *
   * @return TokenParser|string 	a TokenParser instance or its type
   */
	public function current($type = null)
	{ 
		if($type == TYPE){
			$tok = $this->toks[$this->p];
			return $tok::TYPE;
		} else {
			return $this->toks[($type?$this->p+$type:$this->p)]; 
		}
	}

	/**
	 * Parse a stream of TokenParser instances.
	 *
	 * @param  callable 	$callback 	should return false to stop parsing 
	 *
   * @return array 			an array of Node instances
   */
	public function parseToks( $callback )
	{
		$nodes = array();
		$current =& $this->toks[$this->p];

		while( $this->toks[++$this->p] ){
			if(  $callback( $this, $current ) ){
				$this->p--;					
				break;
			}
			$this->parseCurrent($nodes);
		} 

		return( $nodes );
	}

	/**
	 * Parse the current TokenParser stream instance
	 *
	 * @param  	array 			$nodes 		add the parsed Node instance to the array instead of returning it
	 *
   * @return 	Node|null 						a Node instance
   */
	public function parseCurrent( &$nodes = null )
	{		
		$this->toks[$this->p]->group = $this->group;
		$this->toks[$this->p]->scope = $this->scope;

		if( $nodes !== null ){
			if( $node = $this->toks[$this->p]->parse( $this ) ){
				foreach(\SHH\Defaults::toArray($node) as $n){
					$nodes[] = $n;
				}
			}
		} else {
			return( $this->toks[$this->p]->parse( $this ) );
		}
	}

	/**
	 * Parse an array of TokenParser instances.
	 *
	 * @param 	array|null 	an array of TokenParser instances
	 *
   * @return 	array 			an array of Node instance
   */
	public function parse($toks = null)
	{
		$this->toks = $toks;
		$nodes = array();
		$this->p = -1;

		while( $this->toks[++$this->p] ){
			$this->parseCurrent($nodes);
		}

		$this->nodes = $nodes;
		return($nodes);
	}
}