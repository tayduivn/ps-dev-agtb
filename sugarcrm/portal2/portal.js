(function(app) {

    // Add custom events here for now
    app.events.on("app:init", function() {

        // Load dashboard route.
        app.router.route("", "dashboard", function() {
            app.controller.loadView({
                layout: "dashboard"
            });
        });

        // Load the search results route.
        app.router.route("search/:query", "search", function(query) {
            app.controller.loadView({
                mixed: true,
                module: "Search",
                layout: "search",
                query: query,
                silent: true
            });
        });

        // Load the profile
        app.router.route("profile", "profile", function() {
            app.controller.loadView({
                layout: "profile"
            });
        });
        // Loadds profile edit
        app.router.route("profile/edit", "profileedit", function() {
            app.controller.loadView({
                layout: "profileedit"
            });
        });
    });

    var oRoutingBefore = app.routing.before;
    app.routing.before = function(route, args) {
        var dm, nonModuleRoutes;
        nonModuleRoutes = [
            "search",
            "error",
            "profile",
            "profileedit"
        ];

        app.logger.debug("Loading route. " + (route?route:'No route or undefined!'));

        if(!oRoutingBefore.call(this, route, args)) return false;

        function alertUser(msg) {
            // TODO: Error messages should later be put in lang agnostic app strings. e.g. also in layout.js alert.
            msg = msg || "At minimum, you need to have the 'Home' module enabled to use this application.";

            app.alert.show("no-sidecar-access", {
                level: "error",
                title: "Error",
                messages: [msg]
            });
        }

        // Handle index case - get default module if provided. Otherwise, fallback to Home if possible or alert.
        if (route === 'index') {
            dm = typeof(app.config) !== undefined && app.config.defaultModule ? app.config.defaultModule : null;
            if (dm && app.metadata.getModule(dm) && app.acl.hasAccess('read', dm)) {
                app.router.list(dm);
            } else if (app.acl.hasAccess('read', 'Home')) {
                app.router.index();
            } else {
                alertUser();
                return false;
            }
            // If route is NOT index, and NOT in non module routes, check if module (args[0]) is loaded and user has access to it.
        } else if (!_.include(nonModuleRoutes, route) && args[0] && !app.metadata.getModule(args[0]) || !app.acl.hasAccess('read', args[0])) {
            app.logger.error("Module not loaded or user does not have access. ", route);
            alertUser("Issue loading " + args[0] + " module. Please try again later or contact support.");
            return false;
        }
        return true;
    };

    app.view.Field = app.view.Field.extend({
        /**
         * Handles how validation errors are appended to the fields dom element
         *
         * By default errors are appended to the dom into a .help-block class if present
         * and the .error class is added to any .control-group elements in accordance with
         * bootstrap.
         *
         * @param {Object} errors hash of validation errors
         */
        handleValidationError: function(errors) {
            var self = this;
            this.$('.control-group').addClass("error");
            this.$('.help-block').html("");

            // For each error add to error help block
            this.$('.controls').addClass('input-append');
            _.each(errors, function(errorContext, errorName) {
                self.$('.help-block').append(app.error.getErrorString(errorName, errorContext));
            });

            // Remove previous exclamation then add back.
            this.$('.add-on').remove();
            this.$('.controls').find('input').after('<span class="add-on"><i class="icon-exclamation-sign"></i></span>');
        }
    });

    app.Controller = app.Controller.extend({
        loadView: function(params) {
            var self = this;
            // TODO: Will it ever happen: app.config == undefined?
            // app.config should always be present because the logger depends on it
            if ((_.isUndefined(app.config) || (app.config && app.config.appStatus == 'offline')) && params.layout != 'login') {
                var callback = function(data) {
                    var params = {
                        module: "Login",
                        layout: "login",
                        create: true
                    };
                    app.Controller.__super__.loadView.call(self, params);
                    app.alert.show('appOffline', {
                        level: "error",
                        title: 'Error',
                        messages: 'Sorry the application is not available at this time. Please contact the site administrator.',
                        autoclose: false
                    });
                };
                if(app.api.isAuthenticated()) {
                    app.logout({success: callback, error: callback});
                } else {
                    callback();
                }
                return;
            }
            app.Controller.__super__.loadView.call(this, params);
        }
    });

    /**
     * Extends the `save` action to add `portal` specific params to the payload.
     *
     * @param {Object} attributes(optional) model attributes
     * @param {Object} options(optional) standard save options as described by Backbone docs and
     * optional `fieldsToValidate` parameter.
     */
    var __superBeanSave__ = app.Bean.prototype.save;
    app.Bean.prototype.save = function(attributes, options) {
        //Here is the list of params that must be set for portal use case.
        var defaultParams = {
            portal_flag: 1,
            portal_viewable: 1
        };
        var moduleFields = app.metadata.getModule(this.module).fields || {};
        for (var field in defaultParams) {
            if (moduleFields[field]) {
                this.set(field, defaultParams[field], {silent:true});
            }
        }
        //Call the prototype
        __superBeanSave__.call(this, attributes, options);
    };

    var _rrh = {
        /**
         * Handles `signup` route.
         */
        signup: function() {
            app.logger.debug("Route changed to signup!");
            app.controller.loadView({
                module: "Signup",
                layout: "signup",
                create: true
            });
        }
    };

    app.events.on("app:init", function() {
        // Register portal specific routes
        app.router.route("signup", "signup", _rrh.signup);
    });

    /**
     * Checks if there are `file` type fields in the view. If yes, process upload of the files
     *
     * @param {Object} model Model
     * @param {callbacks} callbacks(optional) success and error callbacks
     */
    // TODO: This piece of code may move in the core files
    app.view.View.prototype.checkFileFieldsAndProcessUpload = function(model, callbacks) {

        callbacks = callbacks || {};

        //check if there are attachments
        var $files = _.filter($(":file"), function(file) {
            var $file = $(file);
            return ($file.val() && $file.attr("name") && $file.attr("name") !== "") ? $file.val() !== "" : false;
        });
        var filesToUpload = $files.length;

        //process attachment uploads
        if (filesToUpload > 0) {
            app.alert.show('upload', {level: 'process', title: 'Uploading', autoclose: false});

            //field by field
            for (var file in $files) {
                var $file = $($files[file]),
                    fileField = $file.attr("name");
                model.uploadFile(fileField, $file, {
                    field: fileField,
                    success: function() {
                        filesToUpload--;
                        if (filesToUpload===0) {
                            app.alert.dismiss('upload');
                            if (callbacks.success) callbacks.success();
                        }
                    },
                    error: function(error) {
                        filesToUpload--;
                        if (filesToUpload===0) {
                            app.alert.dismiss('upload');
                        }
                        var errors = {};
                        errors[error.responseText] = {};
                        model.trigger('error:validation:' + this.field, errors);
                        model.trigger('error:validation');
                    }
                });
            }
        }
        else {
            if (callbacks.success) callbacks.success();
        }
    };

})(SUGAR.App);
