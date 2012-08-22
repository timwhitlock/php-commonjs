/**
 * Example CommonJS module.
 */


// any variables in this scope are effectively private
var greeting = "Hello World. It's working!";


/**
 * Expose public methods via the exports object
 */
exports.hello = function(){
    var el = document.createElement('h2');
    el.appendChild( document.createTextNode(greeting) );
    document.body.appendChild( el );
}


/**
 * You can require further modules and ythe compiler will sort the dependency order
 */
var another = require('another-module');

