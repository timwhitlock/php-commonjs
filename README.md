#PHP CommonJS runtime and compiler

###php-commonjs allows you to organise JavaScript into CommonJS style modules within PHP applications.

That means you can do things like: `require('some/module').doStuff()` without any extra async loading of scripts at runtime. 
Then you can compile it all into a single compressed .js file for deploying to your server or CDN.


It consists of two parts:  
 1. a runtime processor (dev)  
 2. a compiler (live)

The runtime processor allows you to debug your code much the same as you normally would. 
It loads your modules as separate js files to aid debugging, and hardly touches your source code during development. 

The deploytime compiler reduces an entire 'application' to a single js file and compresses it with Google's Closure Compiler (included)


## Example

One very simple example is included for now.  

 * `example/example-script.js` is a really simple application that calls in one module.  
 * `example/example-page.php` shows how to run this script during development.  
 * `example/example-build.sh` shows how to use the command line compiler for deployment.  


## Built-in js modules

I haven't added any yet, but I may do. The focus of this project is the compiler, not a JavaScript library.


## Notes and requirements

 * Framework agnostic.
 * No particularly exotic PHP requirements. It's only been tested on PHP 5.3.5.
 * No configuration files. 
 * Java is required to execute Google Closure. It's assumed to be at `/usr/bin/java`.
