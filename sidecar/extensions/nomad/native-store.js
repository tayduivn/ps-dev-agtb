/**
 * Native Key Value Store.
 *
 * @class Nomad.NativeStore
 * @singleton
 * @alias SUGAR.App.nativestore
 */
(function(app) {
    var emptyFn = function() {},
        cache = {};
        
    var _nativeStore = {

        init: function() {
            this.nativeStorePlugin = window.plugins.nativestore;
        },
        
        load: function(callback) {
            this.nativeStorePlugin.getAll(function(metadata) {
                metadata = JSON.parse(metadata);
                cache = metadata;
                if(callback) callback();
            }, emptyFn);
        },
        
        /**
         * Whether the store has a key and the key has a value.
         *
         * @param {String} key Item key.
         * @return {Boolean} True if the key is in the store and has a value.
         */
        has: function(key) {
            return (_.isUndefined(cache[key])) ? false : true;
        },
        
        /**
         * Returns an item from the native store.
         *
         * @param {String} key Item key.
         * @return {String} authentication token for the current user.
         */
        get: function(key) {
            return cache[key];
        },
        
        /**
         * Returns all items.
         * @return {Object}
         */
        getAll: function() {
            return cache;
        },

        /**
         * Puts an item into the native store.
         * @param {String} key Item key.
         * @param {String} value Item to put.
         */
        set: function(key, value) {
            this.nativeStorePlugin.setForKey(key, JSON.stringify(value), emptyFn, emptyFn);
            cache[key] = value;
        },
        
        /**
         * Adds an item to an already existing key.
         * @param {String} key Item key.
         * @param {String} value Item to put.
         */
        add: function(key, value) {
            throw("#add is not yet implemented for native store");
        },

        /**
         * Deletes an item from the native store.
         * @param {String} key Item key.
         */
        cut: function(key) {
            this.nativeStorePlugin.removeForKey(key, emptyFn, emptyFn);
            cache[key] = null;
            delete cache[key];
        },
        
        /**
         * Removes all items from the native store.
         */
        cutAll: function() {
            this.nativeStorePlugin.removeAll(emptyFn, emptyFn);
            _.each(cache, function(value, key) {
               cache[key] = null;
            });
            cache = {};
        }
    };

    app.augment("nativestore", _nativeStore);

})(SUGAR.App);
