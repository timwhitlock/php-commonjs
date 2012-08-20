<?php
/**
 * Cache uses APC
 */
 
if( ! function_exists('apc_store') ){
    trigger_error('php-commonjs requires APC extension', E_USER_ERROR );
} 


class JSCache {



    /**
     * 
     */
    public function cache_source( $hash, $src ){
        if( ! $src ){
            throw new Exception('Refusing to cache empty source '.var_export($src,1).' for '.var_export($hash,1) );
        }
        $key = 'commonjs-source-'.$hash;
        apc_store( $key, $src, 0 );
    }



    /**
     * 
     */
    public function fetch_source( $hash ){
        $key = 'commonjs-source-'.$hash;
        $src = apc_fetch( $key, $ok );
        if( ! $ok || ! $src ){
            return null;
        }
        return $src;
    }        
    
    
    
    /**
     * 
     */
    public function cache_data( array $hashes, array $data ){
        // build a hash of hashes
        $hash = md5( implode('-', $hashes ) );
        $key  = 'commonjs-data-'.$hash;
        apc_store( $key, $data, 0 );
    }   
    


    /**
     * 
     */
    public function fetch_data( array $hashes ){
        $hash = md5( implode('-', $hashes ) );
        $key = 'commonjs-data-'.$hash;
        $data = apc_fetch( $key, $ok );
        if( ! $ok || ! is_array($data) ){
            return null;
        }
        foreach( $data as $datum ){
            extract($datum);
            $deps[ $path ] = $mtime; // add self to timestamp checks
            // validate all dependencies
            foreach( $deps as $path => $mtime ){
                if( ! file_exists($path) ){
                    return null;
                }
                if( filemtime($path) > $mtime ){
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




