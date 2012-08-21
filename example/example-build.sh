#!/bin/bash
# Example of how to use the command line tool to build your php-commonjs scripts into a single file.
# 


# switch to php-commonjs root
#
cd `dirname $0`/..


# you may want to specify a location for php.ini so you don't run into open_basedir restrictions
#
PHP="/usr/bin/php -c /etc"


# pipe compiler output to your destination file
# be sure to pass module search paths so the compiler can find them
#
$PHP -f bin/compile.php -- --compile=example/example-script.js --search=example/modules > example/example-compiled.js


echo "Compiled to `pwd`/example/example-compiled.js"
