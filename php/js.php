<?php
/**
 * Procedural script that outputs JavaScript source code in development mode.
 * URLs to this script are build by CommonJS::get_html.
 * All this script does is pull source code from the cache.
 * The only advantage of using a remote script is to keep JS line numbers intact when debugging.
 */
 

/**
 * Always attempt to exit as JavaScript response
 */
function js_exit( $code = 0 ){
    $src = ob_get_contents();
    header('Content-Type: application/x-javascript; charset=UTF-8', true );
    header('Content-Length: '.strlen($src), true );
    if( ! $code ){
        // allow future expiry. modified scripts will have unique urls
        $ttl = 864000;
        $exp = gmdate('D, d M Y H:i:s', time()+$ttl ).' GMT';
        header('Expires: '.$exp, true );
        header('Pragma: ', true );
        header('Cache-Control: private, max-age='.$ttl, true );
    }
    echo $src;
    exit($code);
}  
 
/**
 * PHP errors will also exit as JavaScript 
 */ 
function js_error( $type, $message, $file, $line, array $args ){
    if( error_reporting() & $type ){
        echo "throw new Error(",json_encode($message),");\n";
        js_exit(1);
    }
}
 
ob_start();
set_error_handler('js_error');
error_reporting( E_ALL ^ E_NOTICE );


try {

    require 'JSCache.php';
    $Cache = new JSCache;
    
    extract( $_GET );
    $src = $Cache->fetch_source( $hash );
    
    if( is_null($src) ){
        $name or $name = $hash;
        throw new Exception('Failed to get cached source code for '.var_export($name,1) );
    }
    
    echo $src;
    js_exit();
      
}
catch( Exception $Ex ){
    echo "throw new Error(",json_encode($Ex->getMessage()),");\n";
    js_exit(1);
}
