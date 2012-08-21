<?php
/**
 * Example PHP page that imports a CommonJS JavaScript application.
 */
 

// establish by your own method whether this is a development or live (compiled) version.
define('MY_DEV_MODE', true );

 
// If in dev mode, require and prep the commonjs compiler
if( MY_DEV_MODE ){
    require '../php/JSCompiler.php';
    // Pass module search paths to constructor
    $Compiler = new JSCompiler('modules');
    // Add your script[s] to the compiler
    $Compiler->add_script('example-script');
    // Generate dev script to output to HTML (may be inline, but remote scripts are better for debugging)
    $inline = false;
    $script = $Compiler->get_html( $inline );
    
}
// else if live mode, point to your compiled code, wherever you put it.
// See ./example-build.sh for how this file was compiled
else {
    $script = '<script src="example-compiled.js"></script>';
}
 
  
 
 
 
?><!DOCTYPE html>
<meta charset="utf-8" />
<html>
<body>
  
<h1>Example CommonJS application</h1>  

<!-- include the generated script tags wherever you see fit -->
<?php echo $script;?> 
    
</body>    
</html>
