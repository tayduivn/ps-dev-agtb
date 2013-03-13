(function(app) {
    var bwcMethods = {
        /**
         * Performs backward compatibility login.
         *
         * The OAuth token is passed and we do automatic in bwc mode by
         * getting a cookie with the PHPSESSIONID.
         */
        login: function(redirectUrl) {
            var url = app.api.buildURL('oauth2', 'bwc/login');
            return app.api.call('create', url, {}, {
                success: function() {
                    app.router.navigate('#bwc/' + redirectUrl, {trigger: true});
                }
            });
        },
        /**
          * Builds a backwards compatible route. For example:
          * #bwc/index.php?module=MyModule&action=DetailView&record12345
          *
          * @param {String} module(required) The name of the module.
          * @param {String} id(optional) The model's ID.
          * @param {String} action(optional) backwards compatible action name.
          * @return {String} route The built route.
          */
         buildRoute: function(module, id, action) {

            /**
             * app.bwc.buildRoute is for internal use and we control its callers, so we're
             * assuming callers will provide the module param which is marked required!
             */
            var href = "#bwc/index.php?";
            var params = { module: module };
            if (!action && !id || action==='DetailView' && !id) {
                params.action = 'index';
            } else {
                if (action) {
                    params.action = action;
                } else {
                    //no action but we do have id
                    params.action = 'DetailView';
                }
                if (id) {
                    params.record = id;
                }
            }
            return href + $.param(params);
         }
    };
    app.augment('bwc', bwcMethods, false);
})(SUGAR.App);
