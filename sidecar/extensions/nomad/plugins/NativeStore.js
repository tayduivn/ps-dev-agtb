function NativeStore() {}

NativeStore.prototype.setForKey = function(key, value) {
    return cordova.exec(null, null, "NativeStore", "setForKey", [key, value]);
};

NativeStore.prototype.getForKey = function(key) {
    return cordova.exec(null, null, "NativeStore", "getForKey", [key]);
};

NativeStore.prototype.removeForKey = function(key) {
    return cordova.exec(null, null, "NativeStore", "removeForKey", [key]);
};

NativeStore.prototype.getAll = function(success, failure) {
    return cordova.exec(success, failure, "NativeStore", "getAll", []);
};

NativeStore.prototype.removeAll = function() {
    return cordova.exec(null, null, "NativeStore", "removeAll", []);
};

cordova.addConstructor(function() {
	if(!window.plugins) window.plugins = {};
	window.plugins.nativestore = new NativeStore();
});