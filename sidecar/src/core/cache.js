/**
 * Persistent cache manager (stash.js)
 * @class Core.CacheMananager
 * @singleton
 * @alias SUGAR.App.cache
 */
(function(app) {
    //For now, just reference stash
    app.augment("cache", stash);
})(SUGAR.App);