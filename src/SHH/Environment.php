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
 * Compiles an input into HTML.
 */
class Environment
{
    const VERSION = '0.9.0';
    public static $compiler;
    protected static $parser; 
    protected static $settings = array();

    /**
     * Constructor.
     *
     * Available options:
     *  * debug:        Prints parser error messages if set to true. (default is false)
     *
     *  * pretty:       Pretty prints the output (default).
     *
     *  * cache:        An absolute path to where to store compiled templates. 
     *                  Cache is disabled if none is specified, set to false or null (default).
     *
     *  * extensions:   An array of extensions. The compiler will accept files with 
     *                  these extensions as well and tries to compile them. By default, 
     *                  the compiler only accepts files with the 'shh' extension (case insensitive).
     *                  
     * 
     * @param   array       $settings   an array of options
     * @param   callable    $lambda     a method
     */
    public function __construct($settings = array())
    { 
        $this->initSettings($settings);
        $this->compiler =& self::$compiler; 
    }

    /**
     * Initialize settings.
     *
     * @see \SHH\Environment::__construct()
     */
    public static function initSettings($settings = array())
    {
        self::$settings = array_merge(self::$settings, $settings);
            
        if(is_bool(self::$settings['pretty']))      \SHH\Defaults::$PRETTY = (bool)self::$settings['pretty'];
        if(is_bool(self::$settings['debug']))       \SHH\Defaults::$ERROR_REPORTING = (bool)self::$settings['debug'];
        if(self::$settings['cache'])                \SHH\CacheController::setCacheDir(self::$settings['cache']);
        if(self::$settings['extensions'])           \SHH\Defaults::$EXTENSIONS = array_merge(\SHH\Defaults::$EXTENSIONS, (array)self::$settings['extensions']);

        if(!self::$compiler) self::$compiler = new \SHH\Compiler();
        if(!self::$parser)   self::$parser =& self::$compiler->parser;
    }

    /**
     * Render a template.
     *
     * @param   string  $file   a template name or shh source string
     * @param   array   $data   an array of parameters in form of:
     *                          array('varName' => 'data');
     *
     * @return string   the rendered template
     */
    public static function render($file, $data = null)
    {
        $__raw = self::getOutput($file);
        unset($file);

        if($data){
            extract((array)$data);
            unset($data);
        } 

        ob_start();  
        eval(" ?>".preg_replace('/<\?(?!php)(\S*)/m', '<_?_$1', $__raw)."<?php "); 
        return preg_replace('/<_\?_/m', '<?', ob_get_clean());
    }

    /**
     * Get the output string.
     * Try to read from cache, otherwise compile source string and create cache if path is defined.
     *
     * @param   string  $file   a template name or a source string
     *
     * @return  string          the output string
     */
    protected static function getOutput($file)
    {
        if(!self::$compiler) self::$compiler = new \SHH\Compiler();

        if( is_file($file) ){
            $path = pathinfo($file);
            if( !preg_grep('/'.$path['extension'].'/i', \SHH\Defaults::$EXTENSIONS) ){
                return file_get_contents($file);

            } else if( self::$settings['cache'] ){
                if( !$html_output = \SHH\CacheController::getCache( $file ) ){
                    $html_output = self::$compiler->compile($file);
                    \SHH\CacheController::writeCache( $file, $html_output );
                } 
                return $html_output;
            } 
        }
        return self::$compiler->compile( $file );
    }

    /**
     * Compile a file or a source string.
     *
     * @param   string  $file   a template name or a source string
     *
     * @return  string          the compiled output string
     */
    public static function compile($file)
    {
        return self::getOutput($file);
    }
}