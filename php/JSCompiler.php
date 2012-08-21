<?php
/**
 * php-commonjs compiler class
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
    
    // queues for parsed and pending scripts during compilation
    private $scripts = array();   
    private $pending  = array();
    private $parsed   = array();
    private $origins  = array();
    private $mtimes   = array();
    private $types    = array();
    private $dependencies = array();
    
    
    /**
     * @param string include paths to module directories
     */
    public function __construct( $search_paths = '' ){
        $this->base = dirname(__FILE__).'/..';
        $this->cwd  = getcwd();
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
        // always include our built-in modules and current directory
        $this->search[] = $this->base.'/js/modules';
        $this->search[] = $this->cwd;
        // prepare cache
        if( ! class_exists('JSCache') ){
            require $this->base.'/php/JSCache.php';
        }
        $this->Cache = new JSCache;
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
     * Compile and generate development mode script tags
     * @param bool whether to fetch inline scripts or remote. inline scripts are harder to debug, but don't rely on the cache.
     * @return string
     */
    public function get_html( $inline = false ){
        $data = $this->compile();
        // generate inline code if specified
        if( $inline ){
            $src = "<script>//<![CDATA[\n";
            // start with common.js helper
            $src .= file_get_contents( $this->base.'/js/common.js' );
            // add all processed source code
            foreach ( $data as $hash => $d ) {
                $src .= "\n\n/* ".basename($d['path'])." */\n".$d['js'];
            }
            $src.= "\n//]]></script>\n";
            return $src;
        }
        // else generate remote scripts to break source code up
        // establish path to web service
        $fullpath = realpath( $this->base.'/php/dev-script.php' );
        $basepath = realpath( $_SERVER['DOCUMENT_ROOT'] );
        $virtpath = str_replace( $basepath, '', $fullpath );
        if( ! $virtpath || $virtpath === $fullpath ){
            throw new Exception('Failed to map js.php to document root');
        }
        // always start with common.js helper script
        $helper = str_replace( 'php/dev-script.php','js/common.js', $virtpath );
        // generate <script> tags pointing to development web service
        $scripts = array (
            '<script src="'.$helper.'"></script>'
        );
        foreach ( $data as $hash => $d ) {
            $url = $virtpath.'?name='.basename($d['path']).'&hash='.$hash.'&modified='.$d['mtime'];
            $scripts[] = '<script src="'.htmlentities($url,ENT_COMPAT,'UTF-8').'"></script>';
        }
        return implode("\n",$scripts);
    }    
    
    
    
    /**
     * 
     */
    public function compile(){
        
        // get hashes for top-level scripts
        $hashes = array();
        foreach( $this->scripts as $i => $path ){
            $path = $this->scripts[$i] = $this->resolve_path( $path );
            $hashes[] = $this->hash( $path );
        }

        // Try to get from cache now we have hashes
        $data = $this->Cache->fetch_data( $hashes );
        if( $data ){
            return $data;
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
                'hash'  => $hash,
                'path'  => $this->origins[$hash],
                'mtime' => $this->mtimes[$hash],
                'deps'  => isset($depcache[$hash]) ? $depcache[$hash] : array(),
                'type'  => isset($this->types[$hash]) ? $this->types[$hash] : 'script',
                'js'    => $this->parsed[$hash],
            );
            $this->Cache->cache_source( $hash, $data[$hash]['js'] );
        }
        
        // cache whole top-level page script
        $this->Cache->cache_data( $hashes, $data );
        
        return $data;
    }    
    
    
    
    
    
    /**
     * 
     */
    private function hash( $path ){
        $hash = md5($path);
        $this->origins[$hash] = $path;
        $this->mtimes[$hash]  = filemtime($path);
        return $hash;
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
            if( ! file_exists($path) ){
                throw new Exception('File not found, '.var_export($path,1) );
            }
            return $path;
        }
        foreach( $this->search as $dir ){
            $abspath = $dir.'/'.$path;
            if( file_exists($abspath) ){
                // found
                return $abspath;
            }
        }
        throw new Exception('File not found, '.var_export($path,1).' in '.var_export(implode(':',$this->search),1) );
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
            $this->types[$hash] = $parse ? 'module' : 'lib';
        }
        // indicate that parent object is dependent on this one
        $this->dependencies[$parent][] = $hash;
        return $hash;
    }




    /**
     * @return array hashes in dependency loading order
     */
    private function _sort_dependencies( $hash, array &$deps ){
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
                        $s = 'CommonJS.'.$req."'$".$modhash."','".$arg."'";
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
            // wrap module code in CommonJS exporter
            $js = "CommonJS.register(".json_encode('$'.$hash).", ".json_encode(basename($path)).", function(exports){\n".$js."\nreturn exports;\n}({}) );\n";
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

