/**
 * Example CommonJS module.
 */

exports.hello = function(){
    var el = document.createElement('h2');
    el.appendChild( document.createTextNode('Hello World') );
    document.body.appendChild( el );
}
