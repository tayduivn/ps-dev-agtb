(function(app) {

    // Add custom events here for now
    app.events.on("app:init", function() {

        // TODO: Check if this is still necessary.
        app.template.compile("alert",
            "<div class=\"{{alertClass}} {{#if autoClose}}timeten{{/if}}\">" +
                "<a class=\"close\" data-dismiss=\"alert\">x</a>{{#if title}}<strong>{{str title}}</strong>{{/if}}" +
                "{{#each messages}}<p>{{str this}}</p>{{/each}}</div>");

        // Override detail/edit view routes
        function recordHandler(module, id, action) {
            var opts = {
                module: module,
                layout: "record",
                action: (action || "detail")
            };

            if (id !== "create") {
                _.extend(opts, {modelId: id});
            } else {
                _.extend(opts, {create: true});
                opts.layout = "newrecord";
            }

            app.controller.loadView(opts);
        }

        app.router.route(":module", "list", function(module) {
            app.controller.loadView({
                module: module,
                layout: "records"
            });
        });

        app.router.route(":module/:id/:action", "record", recordHandler);
        app.router.route(":module/:id", "record", recordHandler);

        // Load dashboard route.
        app.router.route("", "dashboard", function() {
            app.controller.loadView({
                layout: "dashboard",
                module: "ActivityStream",
                title: "My Dashboard",
                skipFetch: true
            });
        });

        // Load dashboard route.
        app.router.route("logout", "logout", function() {
            app.logout({
                complete: function() {
                    window.location.href = 'splash';
                }
            }, true);

        });

        app.router.route("Forecasts", "forecasts", function() {
            app.viewModule = "Forecasts";
            app.controller.loadView({
                module: app.viewModule,
                layout: "index"
            });
        });

        // Load the search results route.
        app.router.route("search/:query", "search", function(query) {
            app.controller.loadView({
                module: "Search",
                layout: "search",
                query: query
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
        var dm,
            nonModuleRoutes = [
                "search",
                "Forecasts",
                "error",
                "profile",
                "profileedit",
                "logout"
            ];

        app.logger.debug("Loading route. " + (route ? route : 'No route or undefined!'));

        if (!oRoutingBefore.call(this, route, args)) return false;

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

    app.loadCss = function(callback) {
        app.api.css(app.config.platform, app.config.themeName, {
            success: function(rsp) {

                if (app.config.loadCss === "url") {
                    var href = app.config.siteUrl + rsp.url;
                    if (app.config.env != "prod") {
                        href += "?t=" + new Date().getTime();
                    }
                    $("<link>")
                        .attr({
                            rel: "stylesheet",
                            type: "text/css",
                            href: href
                        })
                        .appendTo("head");
                }
                else {
                    $("<style>").html(rsp.text).appendTo("head");
                }

                if (_.isFunction(callback)) {
                    callback();
                }
            }
        });
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
            if (_.isUndefined(app.config) || (app.config && app.config.appStatus == 'offline')) {
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
                if (!app.api.isAuthenticated()) {
                    app.logout({success: callback, error: callback}, {clear: true});
                } else {
                    callback();
                }
                return;
            }
            app.Controller.__super__.loadView.call(this, params);
        }
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
                        if (filesToUpload === 0) {
                            app.alert.dismiss('upload');
                            if (callbacks.success) {
                                callbacks.success();
                            }
                        }
                    },
                    error: function(error) {
                        filesToUpload--;
                        if (filesToUpload === 0) {
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
            if (callbacks.success) {
                callbacks.success();
            }
        }
    };

    app._loadAnalytics = function() {
        // Analytics Initialization for browser environments
        Analytics = function () {};

        Analytics.prototype.start = function(id) {
            var timing = window.performance ? window.performance.timing : {};
            _gaq.push(['_setAccount', id]);
            // allow localhost
            _gaq.push(['_setDomainName', 'none']);

            // Set a higher sampling rate since our page hit count is << 1M/day.
            _gaq.push(['_setSampleRate', '80']);
            _gaq.push(['_setSiteSpeedSampleRate', 80]);
            if(!_(timing).isEmpty()) {
                _gaq.push(['_trackTiming', 'Performance API', 'Network latency', timing.responseEnd-timing.fetchStart]);
                _gaq.push(['_trackTiming', 'Performance API', 'Page load', timing.loadEventEnd-timing.responseEnd]);
                _gaq.push(['_trackTiming', 'Performance API', 'Navigation delay', timing.loadEventEnd-timing.navigationStart]);
            }
        };
        Analytics.prototype.trackPageView = function(pageUri) {
            _gaq.push(['_trackPageview', pageUri]);
        };
        Analytics.prototype.trackEvent = function(category,action,label,value) {
            _gaq.push(['_trackEvent', category, action, label, value]);
        };
        Analytics.prototype.setCustomVar = function(index,slot,name,value,scope) {
            _gaq.push(['_setCustomVar',
                slot ? slot : 1,  // This custom var is set to slot #1.  Required parameter.
                name,             // The name acts as a kind of category for the user activity.  Required parameter.
                value,            // This value of the custom variable.  Required parameter.
                scope             // Sets the scope to session-level.  Optional parameter.
            ]);
        };
        analytics = new Analytics();

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    };
})(SUGAR.App);
