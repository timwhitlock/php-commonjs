/**
 * CommonJS runtime helper.
 */ 
var CommonJS = function(){
	/** internal registry of modules indexed by hash of original file name */
    var modules = {};
    /** public CommonJS object - should be global */
    return {
    	/** called by registering module */
        register: function ( hash, filename, mod ){
            modules[hash] = mod;
        },
        /** called to access object */
        require: function ( hash, arg ){
        	var mod = modules[hash];
        	if( ! mod ){
        		throw new Error('CommonJS error: failed to require("'+arg+'")');
        	}
            return mod;
        }
    };
}();
