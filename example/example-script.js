/**
 * Example page script using php-commonjs.
 */

// import commonjs modules with the imaginary require function.
// - do not add a .js extension
var example = require('example-module');


// 'example' reference now holds the 'export' object from the module
example.hello();
