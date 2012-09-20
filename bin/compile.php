<?php
/**
 * php-commonjs command line compiler.
 */
 
define( 'WHICH_JAVA', '/usr/bin/java' );
 
$base = dirname(__FILE__).'/..';
require $base.'/php/Cli.php';
require $base.'/php/JSCompiler.php';

Cli::init();
Cli::register_arg('c', 'compile', 'Script paths to compile; delimit with ":"', true );
Cli::register_arg('s', 'search',  'Search paths for js modules; delimit with ":"', false );
Cli::register_arg('l', 'libs',    'Regular javascript libs to prepend to commonjs code', false );
Cli::register_arg('n', '',        'Disables compression for debugging', false );
Cli::register_arg('d', '',        'Disables cache for debugging', false );
Cli::register_arg('h', 'help',    'Show this help text', false );
Cli::validate_args(); 


try {

    // instantiate copmpiler with optional search paths
    $search_paths = Cli::arg('s');
    $Compiler = new JSCompiler( $search_paths );
    
    // disable cache if -d flag set
    if( Cli::arg('d') ){
        $Compiler->disable_cache();
    }
    
    // add scripts passed
    foreach( explode(':', Cli::arg('c','') ) as $path ){
        $path and $Compiler->add_script( $path );
    }
    
    // add standard libraries
    foreach( explode(':', Cli::arg('l','') ) as $path ){
        $path and $Compiler->add_library($path);
    }
    
    // compile and concatenate full source code
    $src = $Compiler->to_source();
    
    // output uncompressed source if -n flag was specified
    if( Cli::arg('n') ){
        echo $src;
        exit(0);
    }

    // Have uncompressed source code - run it through Closure compiler to compress
    $jar = $base.'/bin/closure/compiler.jar';
    $args = array ( 
        '--compilation_level=SIMPLE_OPTIMIZATIONS',
        '--warning_level=QUIET',
    );
    $src = Cli::exec_jar( $jar, $args, $src ) or Cli::death('Nothing compiled');

    // output compressed code with timestamp banner
    echo "// Compiled ",gmdate('D, d M Y H:i:s')." GMT with php-commonjs \n";
    echo $src;
    exit(0);

}
catch( Exception $Ex ){
    Cli::death( $Ex->getMessage() );
}