/**
 * Example CommonJS module.
 */

exports.hello = function(){
    var el = document.createElement('h2');
    el.appendChild( document.createTextNode("Hello World. It's working.") );
    document.body.appendChild( el );
}
