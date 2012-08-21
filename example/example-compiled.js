// Compiled Tue, 21 Aug 2012 20:46:30 GMT with php-commonjs 
var CommonJS=function(){var b={};return{register:function(a,d,c){b[a]=c},require:function(a,d){var c=b[a];if(!c)throw Error('CommonJS error: failed to require("'+d+'")');return c}}}();CommonJS.register("$1827d93239db8b89fd6d9d85022b4269","example-module.js",function(b){b.hello=function(){var a=document.createElement("h2");a.appendChild(document.createTextNode("Hello World. It's working!"));document.body.appendChild(a)};return b}({}));
var example=CommonJS.require("$1827d93239db8b89fd6d9d85022b4269","example-module");example.hello();
