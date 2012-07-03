(function(app) {

    // One and only instance of user model
    var _usr = new Backbone.Model(),
        // Cache key
        _key = "app:user";

    /**
     * Represents application's current user object.
     *
     * The user object contains settings that are fetched from the server
     * and whatever settings application wants to store.
     *
     * The user object is cached by {@link Core.CacheManager} and is restored at the application start-up.
     * It is updated with data from the server after successful login and cleared upon logout event.
     *
     * <pre><code>
     * // Sample user object that is fetched from the server:
     * {
     *      id: "1",
     *      full_name: "Administrator",
     *      user_name: "admin",
     *      timezone: "America\/Los_Angeles",
     *      datepref: "m\/d\/Y",
     *      timepref: "h:ia"
     * }
     *
     * // Use it like this:
     * var userId = SUGAR.App.user.get('id');
     * // Set app specific settings
     * SUGAR.App.user.set("sortBy:Cases", "case_number");
     *
     * // Bind event handlers if necessary
     * SUGAR.App.user.getUser().on("change", function() {
     *     // Do your thing
     * });
     * 
     * </code></pre>
     *
     * @class Core.User
     * @singleton
     * @alias SUGAR.App.user
     */
    var _user = {
        /**
         * Initializes this user object at application start-up.
         *
         * This method fetches user data stored in the local storage.
         */
        init: function() {
            this._reset(app.cache.get(_key));
        },

        /**
         * Get the current value of an attribute from the user model.
         *
         * For example: `SUGAR.App.get("user_name")`.
         * @param {String} key Attribute key.
         */
        get: function(key) {
            return _usr.get(key);
        },

        /**
         * Sets a hash of attributes (one or many) on a user.
         * You may also pass individual keys and values.
         *
         * See Backbone.Model documentation for details.
         *
         * @param attributes Hash of attributes.
         * @param options(optional) Options.
         */
        set: function(attributes, options) {
            var r = _usr.set(attributes, options);
            app.cache.set(_key, _usr.toJSON());
            return r;
        },

        /**
         * Gets underlying Backbone model instance.
         *
         * You may use this method to get the model instance and attach to its events.
         * @return {Backbone.Model} User model instance.
         */
        getUser: function() {
            return _usr;
        },

        /**
         * Resets user object with new data.
         *
         * @param user(optional) User information object. If not specified this user object is cleared and wiped out from local storage.
         * @private
         */
        _reset: function(user) {
            app.logger.trace(user);
            // Clear local storage if complete reset is requested
            // or the new user that is about to be set has different ID (multiple users per domain are not supported)
            if (!user || (user.id != _usr.id)) {
                _usr.clear({silent:true});
                if (app.cache.has(_key)) {
                    app.cache.cut(_key);
                }
            }

            if (user) {
                this.set(user);
            }
        }
    };

    app.events.on("app:logout", function(clear) {
        if (clear === true) {
            _user._reset();
        }
    });

    app.augment("user", _user, false);

})(SUGAR.App);
