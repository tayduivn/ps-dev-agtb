/**
 * Cordova plugin, adds BrowseURL
 *
 * **BrowseURL provides:**
 *
 * - Method to browse URL in external browser
 *
 * **BrowseURL examples**
 *
 * window.plugins.browseURL.browse("http://google.com");
 *
 */

function BrowseURL() {}

BrowseURL.prototype.browse = function(url){
    console.log ("inside plugin function, calling native");
    cordova.exec(null, null, "BrowseURL", "browse", [url]);
};

cordova.addConstructor(function() {
    if(!window.plugins)
        window.plugins = {};
    window.plugins.browseURL = new BrowseURL();
});
