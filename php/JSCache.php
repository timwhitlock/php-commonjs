<?php
/**
 * File based cache
 */
class JSCache {
    
    /**
     * per-user cache base path
     * @var string
     */
    private $base;     
    
    
    /**
     * 
     */
    public function __construct(){
        // guess available tmp path
        $temp = ini_get('upload_tmp_dir') or $temp = '/tmp';
        // separate cache per user to avoid file permission errors
        $user = $user = $_SERVER['USER'] or $user = trim(`whoami`) or $user = get_current_user();
        // ensure base directory exists and is writeable
        $this->base = $temp.'/php-commonjs/'.$user;
        if( ! is_dir($this->base) ){
            mkdir( $this->base, 0755, true );
        }
        if( ! is_writable($this->base) ){
            throw new Exception('File cache not writeable, '.var_export($this->base,1) );
        }
    }    
    
    

    /**
     * abstraction of cache write
     */
    private function store( $name, $data ){
        if( is_string($data) ){
            $ext = '.js';
        }
        else {
            $ext = '.dat';
            $data = serialize($data);
        }
        $path  = $this->base.'/'.$name.$ext;
        $bytes = file_put_contents( $path, $data, LOCK_EX );
        return $bytes === strlen($data);
    }
    
    
    
    /**
     * Abstraction of cache read
     * @param string cache key
     * @param bool whether data is scalar (source code cache)
     * @param int optionally check cached file isn't older than this
     * @return mixed
     */
    private function fetch( $name, $scalar, $mintime = null ){
        $ext = $scalar ? '.js' : '.dat';
        $path  = $this->base.'/'.$name.$ext;
        if( ! file_exists($path) ){
            return null;
        }
        if( $mintime && filemtime($path) < $mintime ){
            return null;
        }
        $data = file_get_contents($path);
        if( ! $scalar ){
            $data = unserialize($data);
        }
        return $data;
    }     
    
    
    
    /**
     * Retrieve compiled JavaScript source from cache
     * @param string cache key
     * @return string
     */
    public function fetch_source( $name ){
        return $this->fetch( $name, true );
    }    



    /**
     * Retrieve full data for a compiled commonjs application
     * @param string cache key
     * @return array
     */
    public function fetch_data( $name ){
        $data = $this->fetch( $name, false );
        if( ! is_array($data) ){
            return null;
        }
        foreach( $data as $hash => $d ){
            extract($d);
            // validate and add source code cache
            $data[$hash]['js'] = $this->fetch( $hash, true );
            if( ! $data[$hash]['js'] ){
                // source code missing from cache
                return null;
            }
            // add self to checks below
            $deps[ $path ] = $mtime;
            // validate all dependencies and check source file is unmodified
            foreach( $deps as $path => $mtime ){
                if( ! file_exists($path) || filemtime($path) > $mtime ){
                    // no longer valid
                    return null;
                }
            }
        }
        // valid
        return $data;
    }    
    
    
    
    
    /**
     * Cache full data exported from compiler for a commonjs application
     * @param string cache key
     * @param array data returned from JSCompiler::compile
     * @return void
     */
    public function cache_data( $name, array $data ){
        foreach( $data as $hash => $d ){
            // cache source file independently
            $this->store( $hash, $d['js'] );
            unset( $data[$hash]['js'] );
        }    
        // cache remaining array data
        $this->store( $name, $data );
    }
    
    
    
}




