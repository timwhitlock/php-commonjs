<?php
/**
 * php-commonjs compiler class
 * See ../example directory for usage
 */
class JSCompiler {
    
    /**
     * @var JSCache
     */
    private $Cache;     
    
    /**
     * Search paths for finding js modules
     * @var array 
     */
    private $search = array();    
    
   /**
    * Base directory path for php-commonjs
    */
    private $base;     
    
   /**
    * Current working directory when calculating ./ and ../ relative paths
    * @var string
    */    
    private $cwd;
    
    /**
     * queue of top-level scripts added to application
     * @var array
     */
    private $scripts  = array(); 
    
    /**
     * list of regular libraries to prepend common js code, e.g. jQuery
     * @var array
     */
    private $libs = array();    
    
    /**
     * queue of unprocessed js files
     * @var array
     */ 
    private $pending  = array();
    
    /**
     * Source code of processed js files
     * @var array
     */
    private $parsed   = array();
    
    /**
     * Original source code paths indexed by hash
     * @var array
     */
    private $origins  = array();
    
    /**
     * Index of hashes for short, numberic slugs
     * @var array
     */
    private $hashes = array();
    
    /**
     * Source code file modification times indexed by hash
     * @var array
     */
    private $mtimes   = array();
    
    /**
     * unsorted registry of all processed files' dependencies
     * @var array
     */
    private $dependencies = array();
    
    
    /**
     * @param string include paths to module directories
     */
    public function __construct( $search_paths = '' ){
        $this->base = realpath( dirname(__FILE__).'/..' );
        $this->cwd  = realpath( getcwd() );
        if( ! $this->base || ! $this->cwd ){
            throw new Exception('Problem resolving realpaths');
        }
        $paths = is_array($search_paths) ? $search_paths : explode(':', $search_paths );
        foreach( $paths as $path ){
            if( ! $path ){
                continue;
            }
            if( $path{0} !== '/' ){
                $path = $this->cwd.'/'.$path;
            }
            $this->search[] = $path;
        }
        // always include our built-in modules
        $this->search[] = $this->base.'/js/modules';
        // prepare cache
        if( ! class_exists('JSCache') ){
            require $this->base.'/php/JSCache.php';
        }
        $this->Cache = new JSCache;
    }
    
    
    /**
     * Disable cache for debugging purposes
     */
    public function disable_cache(){
        $this->Cache = null;
    }    
    
    
    /**
     * Add top-level script for compilation.
     * @param string js file path relative to PHP's current executing directory 
     * @return JSCompiler
     */
    public function add_script( $path ){
        $this->scripts[] = $path;
        return $this;
    }        
    
    
    
    /**
     * Add a standard already compressed library to prepend scripts
     * @todo support remote libraries
     * @param string js file path relative to PHP's current executing directory 
     * @return JSCompiler
     */
    public function add_library( $path ){
        $path = $this->resolve_path( $path );
        $hash = $this->hash( $path );
        $this->libs[$hash] = $path;
        $this->parsed[$hash] = file_get_contents($path);
        return $this;
    }



    
    /**
     * Compile full, concatenated source code
     * @return string
     */
    public function to_source(){
        $src = '';
        $data = $this->compile();
        // uncompiled libraries
        foreach( $this->libs as $hash => $path ){
            $src .= $data[$hash]['js']."\n\n";
            unset( $data[$hash] );
        }
        // add sandboxing wrapper around compiled code only
        $src .= "( function( window, document, undefined ){\n";
        // add common.js helper
        $src .= file_get_contents( $this->base.'/js/common.js' );
        // add all processed source code
        foreach ( $data as $hash => $d ) {
            $src .= "\n\n/* ".basename($d['path'])." */\n".$d['js'];
        }
        // end sandbox and done
        $src .= "\n} )( window, document );\n";
        return $src;
    }

    
    
    
    /**
     * Compile and generate development mode script tags
     * @param bool whether to fetch inline scripts or remote. inline scripts are harder to debug
     * @return string
     */
    public function get_html( $inline = false ){
        // generate inline code if specified
        if( $inline ){
            $src = "<script>//<![CDATA[\n";
            $src.= $this->to_source();
            $src.= "\n//]]></script>\n";
            return $src;
        }
        // cache is required for remote scripts
        if( ! $this->Cache ){
            throw new Exception('Specify inline scripts if you disable the cache');
        }
        $data = $this->compile();
        // else generate remote scripts to break source code up
        $scripts = array();
        // uncompiled libraries
        foreach( $this->libs as $hash => $path ){
            $url = $this->resolve_virtual($path);
            $scripts[] = '<script src="'.$url.'"></script>';
            unset( $data[$hash] );
        }
        // add common js helper
        $url = $this->resolve_virtual( $this->base.'/js/common.js' );
        $scripts[] = '<script src="'.$url.'"></script>';
        // generate <script> tags pointing to development web service
        $php = $this->resolve_virtual( $this->base.'/php/js.php' );
        foreach ( $data as $hash => $d ) {
            $url = $php.'?name='.basename($d['path']).'&hash='.$hash.'&modified='.$d['mtime'];
            $scripts[] = '<script src="'.htmlentities($url,ENT_COMPAT,'UTF-8').'"></script>';
        }
        return implode("\n",$scripts);
    }    



    /**
     * Generate cache key for against top-level scripts
     * @return string
     */
    private function cache_key(){     
        $hashes = array();
        foreach( $this->scripts as $i => $path ){
            $path = $this->scripts[$i] = $this->resolve_path( $path );
            $hashes[] = $this->hash( $path );
        }
        if( ! $hashes ){
            throw new Exception('Cannot make cache key without scripts');
        }
        return md5( implode($hashes) );
    }
    
    
    
    /**
     * Generate source code for all scripts and dependencies
     * @return array compilation data suitable for various outputs
     */
    public function compile(){
        
        $this->cwd = getcwd();

        // Try to get from cache now we have hashes
        if( $this->Cache ){
            $cachekey  = $this->cache_key();
            $data = $this->Cache->fetch_data( $cachekey );
            if( $data ){
                return $data;
            }
        }
        
        // process scripts and then all dependencies
        $parent = '';
        foreach( $this->scripts as $path ){
            $hash = $this->process( $path, true );
            $parent and $this->dependencies[$hash][] = $parent;
            $parent = $hash;
        }

        // process unparsed dependencies until exhausted
        while( $this->parse_next() );
        
        // collect dependencies and their timestamps before sorting final list
        $depcache = array();
        foreach ( $this->dependencies as $parent => $deps ) {
            foreach( $deps as $h ){
                $depcache[ $parent ][ $this->origins[$h] ] = $this->mtimes[$h];
            }
        }
        
        // sort dependencies for all js files in order
        $deps = array();
        $this->_sort_dependencies( $hash, $deps );

        $data = array();
        foreach( $deps as $hash ){
            $data[$hash] = array (
                'path'  => $this->origins[$hash],
                'mtime' => $this->mtimes[$hash],
                'deps'  => isset($depcache[$hash]) ? $depcache[$hash] : array(),
                'js'    => $this->parsed[$hash],
            );
        }
        
        // cache whole top-level page script
        if( $this->Cache ){
            $this->Cache->cache_data( $cachekey, $data );
        }
        
        return $data;
    }    
    
    
    
    
    
    /**
     * Generate a hash for a full file path and store original path with modified time
     * @param string absolute and already resoved file path
     * @return string unique hash to use against this file in caches
     */
    private function hash( $path ){
        $hash = md5($path);
        $this->origins[$hash] = $path;
        if( ! isset($this->hashes[$hash]) ){
            $this->mtimes[$hash]  = filemtime($path);
            $this->hashes[$hash] = count($this->hashes);
        }
        return $hash;
    }
    
    
    
    /**
     * Resolve a local path toa virtual one
     */
    private function resolve_virtual( $path ){
        static $basepath;
        if( ! isset($basepath) ){
            $basepath = realpath( $_SERVER['DOCUMENT_ROOT'] );
        }
        $virtpath = str_replace( $basepath, '', $path );
        if( ! $virtpath || $virtpath === $path ){
            throw new Exception('Failed to map path  to document root, '.$path);
        }    
        return $virtpath;
    }

    
    
    /**
     * Resolve full path to a .js file against include paths and current working directory
     * @param string
     * @return string
     */
    private function resolve_path( $path ){
        if( ! $path ){
            throw new Exception('Path is empty');
        }
        // ensure .js extension
        if( '.js' !== substr($path,-3) ){
            $path .= '.js';
        }
        // path may be absolute, but must still exist
        if( '/' === $path{0} ){
            $abspath = realpath($path);
            if( ! $abspath ){
                throw new Exception('File not found, '.var_export($path,1) );
            }
            return $abspath;
        }
        // add current working directory to search paths
        $search   = $this->search;
        $search[] = $this->cwd;
        foreach( $search as $dir ){
            $abspath = realpath( $dir.'/'.$path );
            if( $abspath && file_exists($abspath) ){
                return $abspath;
            }
        }
        throw new Exception('File not found, '.var_export($path,1).' in '.var_export(implode(':',$search),1) );
    }     




    /**
     * @param string module path; e.g. "./lib/stuff"
     * @param string parent object
     * @return string hash
     */
    public function register_dependency( $path, $parent ){
        // files with js extension will not be parsed - assumed libs
        if( '.js' === substr($path,-3) ){
            $parse = false;
        }
        else {
            $parse = true;
        }
        $path = $this->resolve_path( $path );
        $hash = $this->hash( $path );
        // set as pending if not yet parsed
        if( ! isset($this->parsed[$hash]) ){
            $this->pending[$hash] = $parse;
        }
        // indicate that parent object is dependent on this one
        $this->dependencies[$parent][] = $hash;
        return $hash;
    }




    /**
     * @return array hashes in dependency loading order
     */
    private function _sort_dependencies( $hash, array &$deps ){
        // prepend dependencies with standard uncompiled libraries
        if( ! $deps ){
            $deps = array_keys($this->libs);
        }
        while( isset($this->dependencies[$hash]) && $dep = array_shift($this->dependencies[$hash]) ){
            $this->_sort_dependencies( $dep, $deps );
        }
        unset($this->dependencies[$hash]);
        // else no more dependencies
        in_array($hash,$deps) or $deps[] = $hash;
    }




    /**
     * Process JavaScript file to source code and collect CommonJS dependencies
     */
    private function process( $path, $parse ){
        $path = $this->resolve_path( $path );
        $hash = $this->hash( $path );
        $src = file_get_contents( $path );
        if( ! $src ){
            throw new Exception('Source file empty at '.$path);
        }
        if( ! $parse ){
            $js = $src;
        }
        else {
            // lazy include of parser classes
            if( ! class_exists('JSTokenizer') ){
                require $this->base.'/php/JSLex.php';
                require $this->base.'/php/JSTokenizer.php';
            }
            // Simple parsing using only a token stream
            // any string literal following sequence "require" J_WHITESPACE "(" J_WHITESPACE
            // will be taken to mean a require function call
            $this->cwd = dirname($path);
            $Stream = new JSTokenizer;
            $Stream->init( $src );
            $js = '';
            while( $tok = $Stream->get_next_token() ){
                list( $t, $s ) = $tok;
                if( isset($req) ){
                    // require statement has started - check for string argument when ready
                    if( $t === J_STRING_LITERAL && false !== strpos($req,'(') ){
                        $arg = trim( $s, "'" );
                        $modhash = $this->register_dependency( $arg, $hash );
                        // alter require statement to use our internal ID - adding js file for debugging
                        $id = json_encode('$'.$this->hashes[$modhash] );
                        $fn = json_encode( basename( $this->origins[$modhash] ) );
                        $s  = 'CommonJS.'.$req.$id.",".$fn;
                        unset($req);
                    }
                    // ensure it's a valid "require (" sequence
                    else if( $t !== J_WHITESPACE && $s !== '(' ){
                        $s = $req.$s;
                        unset($req);
                    }
                    // else still collecting tokens
                    else {
                        $req .= $s;
                        continue;
                    }
                }
                else if( $t === J_IDENTIFIER && $s === 'require' ){
                    $req = $s;
                    continue;
                }
                // add source to output
                $js .= $s;
            }
        }    
        // May be parsing a pending module
        if( isset($this->pending[$hash]) ){
            unset( $this->pending[$hash] );
            // wrap module code in CommonJS exporter using internal ID
            $id = json_encode('$'.$this->hashes[$hash]);
            $js = "CommonJS.register(".$id.", function(exports){\n".$js."\nreturn exports;\n}({}) );\n";
        }
        // save source
        $this->parsed[$hash] = $js;
        return $hash;
    }

    
    
    
    /**
     * parse the next registered, but unparsed dependency
     */
    private function parse_next(){
        foreach( $this->pending as $h => $parse ){
            $local = $this->origins[$h];
            return $this->process( $local, $parse );
        }
        return '';
    }    
    

         
}

