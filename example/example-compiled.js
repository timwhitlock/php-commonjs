// Compiled Tue, 21 Aug 2012 19:55:51 GMT with php-commonjs 
var CommonJS=function(){var b={};return{register:function(a,d,c){b[a]=c},require:function(a,d){var c=b[a];if(!c)throw Error('CommonJS error: failed to require("'+d+'")');return c}}}();CommonJS.register("$bd0169f2aa0bc4ca15f2e36ab96de0bc","example-module.js",function(b){b.hello=function(){var a=document.createElement("h2");a.appendChild(document.createTextNode("Hello World. It's working!"));document.body.appendChild(a)};return b}({}));
var example=CommonJS.require("$bd0169f2aa0bc4ca15f2e36ab96de0bc","example-module");example.hello();
