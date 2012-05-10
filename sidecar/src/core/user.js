(function(app) {

    /**
     * Represents application's current user object.
     *
     * Sample user object:
     * <pre><code>
     *
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
     * 
     * </code></pre>
     *
     * @class Core.User
     * @singleton @extends Backbone.Model
     * @alias SUGAR.App.user
     */
    var usr, _user;
    usr = new Backbone.Model({});

    _user = {
        /**
         * Initializes this user object at application start-up.
         *
         * This method fetches user data stored in the local storage.
         */
        init: function() {
            var user, s;
            try {
                // We serialize ourselves because stash.js stringifies everything (functions included)
                s = app.cache.get("current_user");
                if (s) user = JSON.parse(s);
            }
            catch (e) {
                app.logger.error("Failed to read user object from cache:\n" + e);
            }
            this._reset(user);
        },

        get: function(key) {
            return usr.get(key);
        },
        
        set: function(key, value) {
            return usr.set(key, value);
        },

        /**
         * Resets user object with new data.
         *
         * @param user(optional) User information object. If not specified this user object is cleared and wiped out from local storage.
         * @param  
         * @return {Boolean} Flag indicating if the reset was successful.
         * @private
         */
        _reset: function(user) {
            var r = true;
            usr.clear({silent:true});

            if (user) {
                usr.set(user); 
                try {
                    user = JSON.stringify(usr.toJSON());
                    r = app.cache.set("current_user", user);
                }
                catch (e) {
                    app.logger.error("Failed to set user object into cache:\n" + e);
                    r = false;
                }
            }
            return r;
        }
    };

    app.events.on("app:login:success", function(data) {
        _user._reset(data ? data.current_user : null);
    }).on("app:logout", function(clear) {
        if(clear) {
            _user._reset();
        }
    });

    app.augment("user", _user);

})(SUGAR.App);
