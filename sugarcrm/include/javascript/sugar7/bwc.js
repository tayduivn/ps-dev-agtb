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
          * bwc/index.php?module=MyModule&action=DetailView&record12345
          *
          * @param {String} module(required) The name of the module.
          * @param {String} id(optional) The model's ID.
          * @param {String} action(optional) backwards compatible action name.
          * @param {Object} params(optional) Additional URL parameters. Should not include id/module/action.
          * @return {String} route The built route.
          */
         buildRoute: function(module, id, action, params) {

            /**
             * app.bwc.buildRoute is for internal use and we control its callers, so we're
             * assuming callers will provide the module param which is marked required!
             */
            var href = "bwc/index.php?";
            var params = _.extend({}, { module: module }, params);
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
         },

        /**
         * For BWC modules, we need to get URL params for creating the related record
         * @returns {Object} BWC URL parameters
         * @private
         */
        _createRelatedRecordUrlParams: function(parentModel, link) {
            var params = {
                parent_type: parentModel.module,
                parent_name: parentModel.get("name"),
                parent_id: parentModel.get("id"),
                return_module: parentModel.module,
                return_id: parentModel.get("id"),
                return_name: parentModel.get("name")
            };
            //Set relate field values as part of URL so they get pre-filled
            var fields = app.data.getRelateFields(parentModel.module, link);
            _.each(fields, function(field){
                params[field.name] = parentModel.get(field.rname);
                params[field.id_name] = parentModel.get("id");
                if(field.populate_list) {
                    // We need to populate fields from parent record into new related record
                    _.each(field.populate_list, function (target, source) {
                        source = _.isNumber(source) ? target : source;
                        if (!_.isUndefined(parentModel.get(source))) {
                            params[target] = parentModel.get(source);
                        }
                    }, this);
                }
            });
            return params;
        },

        /**
         * Route to Create Related record UI for a BWC module
         */
        createRelatedRecord: function(module, parentModel, link) {
            var params = this._createRelatedRecordUrlParams(parentModel, link);
            var route = app.bwc.buildRoute(module, null, "EditView", params);
            app.router.navigate("#" + route, {trigger: true}); // Set route so that we switch over to BWC mode
        }
    };
    app.augment('bwc', bwcMethods, false);
})(SUGAR.App);
