function Keychain() {}

Keychain.prototype.getForKey = function(key, serviceName, success, failure) {
    if(_.isEmpty(key) || _.isEmpty(serviceName)) {
        failure();
    } else {
        return cordova.exec(success, failure, "Keychain", "getForKey", [key, serviceName]);
    }
};

Keychain.prototype.setForKey = function(key, value, serviceName, success, failure) {
    if(_.isEmpty(key) || _.isEmpty(value)) {
        failure();
    } else {
        return cordova.exec(success, failure, "Keychain", "setForKey", [key, value, serviceName]);
    }
};

Keychain.prototype.removeForKey = function(key, serviceName, success, failure) {
    if(_.isEmpty(key) || _.isEmpty(serviceName)) {
        failure();
    } else {
        return cordova.exec(success, failure, "Keychain", "removeForKey", [key, serviceName]);
    }
};

cordova.addConstructor(function() {
	if(!window.plugins) window.plugins = {};
	window.plugins.keychain = new Keychain();
});