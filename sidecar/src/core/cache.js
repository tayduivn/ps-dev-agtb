/**
 * Persistent cache manager.
 *
 * By default, cache manager uses stash.js to manipulate items in `window.localStorage` object.
 * Use {@link Core.CacheManager#store} property to override the storage provider.
 * The value of the key which is passed as a parameter to `get/set/add` methods is prefixed with
 * `<env>:<appId>:` string to avoid clashes with other environments and applications running off the same domain name and port.
 * You can set environment and application ID in {@link Config} module.
 *
 * @class Core.CacheMananager
 * @singleton
 * @alias SUGAR.App.cache
 */
(function(app) {

    var _keyPrefix = "";

    var _buildKey = function(key) {
        return _keyPrefix + key;
    };

    var _cache = {

        /**
         * Storage provider.
         *
         * Default: stash.js
         *
         * @cfg {Object}
         */
        // Not all stash.js's methods are available on cache module
        // We can add additional methods later if we need them (get, set, cut are the most used)
        store: stash,

        /**
         * Initializes cache manager.
         */
        init: function() {
            _keyPrefix = app.config.env + ":" + app.config.appId + ":";
        },

        /**
         * Checks if the item exists in cache.
         * @param {String} key Item key.
         */
        has: function(key) {
            // Only if we're in fact using the stash.js lib do we directly shim the has method. 
            // Otherwise, we delegate out to whatever this.store.has is.
            if(this.store === stash) {
                return window.localStorage.getItem(_buildKey(key)) !== null;
            } else {
                this.store.has(_buildKey(key));
            }
        },

        /**
         * Gets an item from the cache.
         * @param {String} key Item key.
         * @return {Function/Number/Boolean/String/Array/Object} Item with the given key.
         */
        get: function(key) {
            return this.store.get(_buildKey(key));
        },

        /**
         * Puts an item into cache.
         * @param {String} key Item key.
         * @param {Function/Number/Boolean/String/Array/Object} value Item to put.
         */
        set: function(key, value) {
            this.store.set(_buildKey(key), value);
        },

        /**
         * Add an item to an existing item.
         * @param {String} key Item key.
         * @param {Function/Number/Boolean/String/Array/Object} value Item to add.
         */
        add: function(key, value) {
            this.store.add(_buildKey(key), value);
        },

        /**
         * Deletes an item from cache.
         * @param {String} key Item key.
         */
        cut: function(key) {
            this.store.cut(_buildKey(key));
        },

        /**
         * Deletes all items from cache.
         *
         * By default, this method deletes all items for the current app and environment.
         * Pass `true` to this method to remove all items.

         * @param {Boolean} all(optional) Flag indicating if all items must be deleted from this cache.
         */
        cutAll: function(all) {
            if (all === true) return this.store.cutAll();
            _.each(this.store.getAll(), function(value, key) {
                if (key.indexOf(_keyPrefix) === 0) {
                    this.store.cut(key);
                }
            }, this);
        }

    };

    app.augment("cache", _cache);


})(SUGAR.App);
