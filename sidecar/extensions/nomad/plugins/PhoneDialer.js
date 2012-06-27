function PhoneDialer() {}

PhoneDialer.prototype.dial = function(phoneNumber) {
    return cordova.exec(null, null, "PhoneDialer", "dial", [phoneNumber]);
};

cordova.addConstructor(function() {
	if(!window.plugins) window.plugins = {};
	window.plugins.phonedialer = new PhoneDialer();
});