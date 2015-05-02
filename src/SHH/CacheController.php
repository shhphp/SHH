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
 * Cache controller.
 */
class CacheController
{
    const SUFFIX = '.cache';
    protected static $cacheDir;

    /**
     * Set the cache directory.
     *
     * @param   string  $dir    An absolute path to where to store compiled templates.
     */
    public static function setCacheDir($dir)
    {
        self::$cacheDir = (substr($dir, -1) == DIRECTORY_SEPARATOR)? $dir : $dir.DIRECTORY_SEPARATOR;
    }

    /**
     * Get the content of a cached template if it exists.
     *
     * @param   string  $file   the name of the template file
     *
     * @return  string|null     the content of a cached template
     */
    public static function getCache($file)
    {
        if( is_file($cacheFile = self::$cacheDir.$file.self::SUFFIX) ){
            if( filemtime($file) < filemtime($cacheFile) ){
                return file_get_contents($cacheFile);
            }
        }
    } 

    /**
     * Write a cache file.
     *
     * @param   string  $file       the name of the template file
     * @param   string  $content    the content to write to the cache file
     */
    public static function writeCache( $file, $content )
    {
        $filedir = dirname($file); 
        $filedir = ($filedir == DIRECTORY_SEPARATOR || $filedir == '.')? null : $filedir.DIRECTORY_SEPARATOR;

        if(!is_dir($cacheFileDir = self::$cacheDir.$filedir)){
            if( !@mkdir($cacheFileDir, 0755, true) ) { 
                throw new \RuntimeException(sprintf("Unable to write in the cache directory (%s).", $cacheFileDir));
            } 
        }
        file_put_contents( $cacheFileDir.basename($file).self::SUFFIX, $content, LOCK_EX );
    }  
}