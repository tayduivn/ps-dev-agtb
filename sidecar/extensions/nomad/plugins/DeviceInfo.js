/**
 * Cordova plugin, adds DeviceInfo
 *
 * **DeviceInfo provides:**
 *
 * - Method to gather device info
 *
 * **DeviceInfo examples**
 *
 * function onDeviceInfo(info){
 *      ...
 * }
 *
 * window.plugins.deviceInfo.getDeviceInfo(onDeviceInfo);
 *
 * **Callback function argument**
 *
 *  info.uuid         -   Device unique identifier
 *  info.model        -   Device model
 *  info.osVersion    -   OS version
 *  info.carrier      -   Carrier
 *  info.appVersion   -   App(Bundle) Version
 *
 */

function DeviceInfo() {}

DeviceInfo.prototype.getDeviceInfo = function(callback){
    cordova.exec(callback, callback, "DeviceInfo", "getDeviceInfo", [{uuid: device.uuid, osVersion: device.version}]);
};

cordova.addConstructor(function() {
    if(!window.plugins)
        window.plugins = {};
    window.plugins.deviceInfo = new DeviceInfo();
});