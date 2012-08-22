/**
 * CommonJS runtime helper.
 */ 
var CommonJS = function(){
    // internal registry of modules indexed by uniqiue internal ID */
    var modules = {};
    // expose public CommonJS object into scope
    return {
        // called by self-registering module
        register: function ( hash, mod ){
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
