<?php
/**
 * Cache uses APC
 */
 
if( ! function_exists('apc_store') ){
    trigger_error('php-commonjs requires APC extension', E_USER_ERROR );
} 


class JSCache {
    
    
    /**
     * abstraction of store
     */
    private function store( $key, $data ){
        if( ! apc_store( $key, $data, 0 ) ){
            throw new Exception('Failed to cache '.getype($data).' at '.var_export($key,1) );
        }
        return true;
    }     
    
    
    /**
     * Abstraction of fetch
     */
    private function fetch( $key ){
        $value = apc_fetch( $key, $ok );
        if( ! $ok ){
            //throw new Exception('oops');
            return null;
        }
        return $value;
    }     


    /**
     * 
     */
    public function cache_source( $hash, $src ){
        $key = 'commonjs-source-'.$hash;
        return $this->store( $key, $src );
    }



    /**
     * 
     */
    public function fetch_source( $hash ){
        $key = 'commonjs-source-'.$hash;
        $src = $this->fetch($key) or $src = null;
        return $src;
    }        
    
    
    
    /**
     * 
     */
    public function cache_data( array $hashes, array $data ){
        // build a hash of hashes
        $hash = md5( implode('-', $hashes ) );
        $key  = 'commonjs-data-'.$hash;
        return $this->store( $key, $data );
    }   
    


    /**
     * 
     */
    public function fetch_data( array $hashes ){
        $hash = md5( implode('-', $hashes ) );
        $key = 'commonjs-data-'.$hash;
        $data = $this->fetch($key);
        if( ! is_array($data) ){
            return null;
        }
        foreach( $data as $datum ){
            extract($datum);
            $deps[ $path ] = $mtime; // add self to timestamp checks
            // validate all dependencies
            foreach( $deps as $path => $mtime ){
                if( ! file_exists($path) || filemtime($path) > $mtime ){
                    // no longer valid
                    apc_delete( $key );
                    return null;
                }
            }
            // validate all source code caches
            //if( ! $this->fetch_source($hash) ){
            //    return null;
            //}
        }
        // valid
        return $data;
    }    

    
    
    
}




