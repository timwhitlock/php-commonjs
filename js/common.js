/**
 * CommonJS runtime helper.
 */ 
var CommonJS = function(){
    // internal registry of modules indexed by hash of original file name */
    var modules = {};
    // expose public CommonJS object - should be global
    return {
        // called by registering module
        register: function ( hash, filename, mod ){
            modules[hash] = mod;
        },
        // called to access module's export object
        require: function ( hash, filename ){
        	var mod = modules[hash];
        	if( ! mod ){
        		throw new Error('CommonJS error: failed to require("'+filename+'")');
        	}
            return mod;
        }
    };
}();
