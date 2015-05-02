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
 * Compiles the input into HTML.
 *
 * Highlevel structure of the SHH Compiler:
 * 		<-[HTML]- Compiler <-[HTML]- Dumper <-[Nodes]- Parser <-[TokenParsers]- Lexer <-[SHH]-
 */
class Compiler
{
	public static $parser;
	public static $file; 
	public static $preprocessors = array();

	/**
	 * Constructor.
   */
	public function __construct()
	{
		$this->initParser();
		$this->parser =& self::$parser;
	}

		/**
     * @see \SHH\Parser::interpolation()
     */
    public static function interpolation($regex, $callback)
    {
       self::$parser->interpolation($regex, $callback);
    }

    /**
     * @see \SHH\Parser::filter()
     */
    public static function filter($key, $open = null, $close = null, $callback = null)
    {
       self::$parser->filter($key, $open, $close, $callback);
    }

    /**
     * @see \SHH\Parser::autotag()
     */
    public static function autotag($key, $element, $callback, $parent = null)
    {
       self::$parser->autotag($key, $element, $callback, $parent);
    }

    /**
     * @see \SHH\Parser::directive()
     */
    public static function directive($key, $callback = null)
    {
       self::$parser->directive($key, $callback);
    }

	/**
	 * Compile a file or a source string.
	 *
	 * @param 	string 	$file 	source string or file name
	 * @param 	bool 		$raw 		set to true if source code is passed instead of a file
	 *
	 * @return 	string 	the compiled output string
   */
	public static function compile($file, $raw = false)
	{	
    self::initParser();
    $parser = clone self::$parser;
    self::$file = null;

    if( is_file($file) ){
			if( self::isHtml($input = file_get_contents($file)) ) return $input;
			self::$file = $file;
		} else {
			if( self::isHtml($file) ) return $file;
			if( !$raw && preg_match('/^\S+\.[a-z]+$/i', $file) ){
				echo "<br><b>SHH warning</b>: file <b>$file</b> could not be found.";
			} else {
				$input = $file;
			}
		}

		foreach(self::$preprocessors as $preprocessor){
			if( preg_match($preprocessor[0], $input, $match) ){
				if( $out = $preprocessor[1]($input, \SHH\Defaults::strToArray($match[2])) ){
					$input = $out;
				}
				if( !$preprocessor[3] ){
					$input = preg_replace($preprocessor[0], null, $input);
				} else {
					self::$parser->directive->add($preprocessor[0]);
				}
			}
		}

		$input = preg_replace('/<\?[a-z]*|\?>/i', '?', $input);
    $parser->parse( \SHH\Lexer::tokenize( $input ) ); 
    return \SHH\Dumper::dump($parser); 
  }

  /**
	 * Check if input is HTML.
	 *
	 * @param 	string 	$input 	the input string to test
	 *
	 * @return 	string 	returns the input string match
   */
  protected static function isHtml($input)
  {
  	if( preg_match("/^\s*<[\s\S]+>\s*$/", $input) ){
			return $input;
		}
  }

  /**
	 * Add a preprocessor.
	 *
	 * @param 	string 		$key 				the name of the directive
	 * @param 	callable 	$callable 	a method which takes an array as argument
	 * @param 	bool 			$remove 		remove the directive from the input string if true
   */
  public static function preprocessor($key, $callable = null, $remove = true){
  	$regex = '/^\s*!\s*('.$key.')(.*)/m';
  	self::$preprocessors[$key] = array($regex, $callable, $remove);
  }

  /**
	 * Return file name.
	 *
	 * @return 	string|bool
   */
	public static function getFile()
	{
		return(self::$file);
	}

  /**
	 * Initialize parser.
   */
	protected static function initParser()
	{
		if(!self::$parser){
	    self::$parser = new \SHH\Parser();

	    self::preprocessor('tabsize', function($source, $args){
				if( is_numeric($args[0]) ){
		      \SHH\Defaults::setTabSize( (int)$args[0] );
		    }
	    });

	   	self::$parser->interpolation('/\?([\s\S]*)\?/m', function($match){
	    	return "<?php ".trim($match[0])." ?>"; 
	    });

	   	self::$parser->interpolation('/(?<!<\?php )(?:\${ *(\S+) *}|{ *(\$\S+) *}|\$([^ .;]+))/m', function($match){
	   		return "<?php echo $".trim(implode($match),'$')."; ?>"; 	
	    });

	    self::$parser->filter("js",  "<script type='text/javascript'>", "</script>" );
	    self::$parser->filter("css", "<style type='text/css'>", "</style>" );
	    self::$parser->filter("php", "<?php ", " ?>" );
	    self::$parser->filter("code","<pre><code>", "</code></pre>" );

	    self::$parser->directive('html');
	    self::$parser->directive->set('autotag');
	    
	    self::$parser->directive('doctype', function(&$parser, $args){
	    		$declaration = $args[0]?$args[0]:'html';
        	$doc = $parser->doctypes[$declaration];
          $declaration = $doc ? $doc : $declaration;
          $parser->directive->set(DOCTYPE, $declaration);
	    });

	    self::$parser->directive('xml', function(&$parser, $args){
	    	$options = array('version' => '1.0');
	      foreach($args as $a){
	        if($a == 'yes' || $a == 'no' || $a == 'false' || $a =='true' ){
	          $options['standalone'] = (($a == 'yes' || $a == 'true')? true : false);
	     	 	} else if(is_numeric($a)){
	          $options['version'] = number_format($a, 1, '.', '');
	     	 	} else {
	          $options['encoding'] = $a;
	        }
	      }
	      $parser->directive->set(XML, $options);
	    });

			self::$parser->directive('use', function(&$parser, $args, $tok){
	      if( is_file(  $file = trim($args[0], '\'"') )){
	      	$compiler = new Compiler();

	      	$directive = clone $parser->directive;
	      	$parser->directive->removeAll();

	      	$toks = $compiler->parser->parse( \SHH\Lexer::tokenize( file_get_contents($file) ));
	      	$parser->directive = $directive;

	      	return( $toks );	
	      } else {
	      	new \SHH\ParseError(sprintf("file %s doesn't exist", trim($args[0], '\'"') ), $tok );
	      }
	    });

			self::$parser->autotag(array('css', 'style'), function($arg){
	      return array(
	        'type'=> 'text/css'
	      );
	    }, 'style', 'head');

	    self::$parser->autotag('link', function($arg){
	      return array(
	        'rel' => 'stylesheet',
	        'type'=> 'text/css',
	        'href'=> $arg
	      );
	    }, 'link', 'head');

	    self::$parser->autotag(array('js', 'script'), function($arg){
	      return array(
	        'type'=> 'text/javascript',
	        'src' => $arg
	      );
	    }, 'script', array('head', 'body') );

	    self::$parser->autotag('keywords', function($arg){
			  return array(
			    'name'=>'keywords',
			    'content'=>$arg
			  );
			}, 'meta', 'head');

			self::$parser->autotag(array('description', 'descr'), function($arg){
			  return array(
			    'name'=>'description',
			    'content'=>$arg
			  );
			}, 'meta', 'head');

			self::$parser->autotag('robots', function($arg){
			  return array(
			    'name'=>'robots',
			    'content'=>$arg
			  );
			}, 'meta', 'head');

			self::$parser->autotag('copyright', function($arg){
			  return array(
			    'name'=>'copyright',
			    'content'=>$arg
			  );
			}, 'meta', 'head');

			self::$parser->autotag('author', function($arg){
			  return array(
			    'name'=>'author',
			    'content'=>$arg
			  );
			}, 'meta', 'head');
  	}
  }
}
