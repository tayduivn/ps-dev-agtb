(function(app) {

    // Add custom events here for now
    app.events.on("app:init", function() {
        // app.data.declareModels();

        // Override detail/edit view routes
        function recordHandler(module, id, action) {
            console.log("Routing recordhandler");

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

        app.router.route(":module/:id/:action", "record", recordHandler);
        app.router.route(":module/:id", "record", recordHandler);

        // Load dashboard route.
        app.router.route("", "dashboard", function () {
            app.controller.loadView({
                layout: "dashboard",
                module: "ActivityStream"
            });
        });

        // Load dashboard route.
        app.router.route("logout", "logout", function () {
            app.logout({
                complete:function () {
                    window.location.href = 'splash';
                }
            }, true);

        });

        // Load the search results route.
        app.router.route("search/:query", "search", function (query) {
            app.controller.loadView({
                module:"Search",
                layout:"search",
                query:query
            });
        });

        // Load the profile
        app.router.route("profile", "profile", function () {
            app.controller.loadView({
                layout:"profile"
            });
        });

        // Loadds profile edit
        app.router.route("profile/edit", "profileedit", function () {
            app.controller.loadView({
                layout:"profileedit"
            });
        });
    });

    var oRoutingBefore = app.routing.before;
    app.routing.before = function (route, args) {
        var dm, nonModuleRoutes;
        nonModuleRoutes = [
            "search",
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
                level:"error",
                title:"Error",
                messages:[msg]
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
        handleValidationError:function (errors) {
            var self = this;
            this.$('.control-group').addClass("error");
            this.$('.help-block').html("");

            // For each error add to error help block
            this.$('.controls').addClass('input-append');
            _.each(errors, function (errorContext, errorName) {
                self.$('.help-block').append(app.error.getErrorString(errorName, errorContext));
            });

            // Remove previous exclamation then add back.
            this.$('.add-on').remove();
            this.$('.controls').find('input').after('<span class="add-on"><i class="icon-exclamation-sign"></i></span>');
        }
    });

    app.Controller = app.Controller.extend({
        loadView:function (params) {
            var self = this;
            // TODO: Will it ever happen: app.config == undefined?
            // app.config should always be present because the logger depends on it
            if (_.isUndefined(app.config) || (app.config && app.config.appStatus == 'offline')) {
                var callback = function (data) {
                    var params = {
                        module:"Login",
                        layout:"login",
                        create:true
                    };
                    app.Controller.__super__.loadView.call(self, params);
                    app.alert.show('appOffline', {
                        level:"error",
                        title:'Error',
                        messages:'Sorry the application is not available at this time. Please contact the site administrator.',
                        autoclose:false
                    });
                };
                if(!app.api.isAuthenticated()) {
                    app.logout({success: callback, error: callback}, {clear:true});
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
        var $files = _.filter($(":file"), function (file) {
            var $file = $(file);
            return ($file.val() && $file.attr("name") && $file.attr("name") !== "") ? $file.val() !== "" : false;
        });
        var filesToUpload = $files.length;

        //process attachment uploads
        if (filesToUpload > 0) {
            app.alert.show('upload', {level:'process', title:'Uploading', autoclose:false});

            //field by field
            for (var file in $files) {
                var $file = $($files[file]),
                    fileField = $file.attr("name");
                model.uploadFile(fileField, $file, {
                    field:fileField,
                    success:function () {
                        filesToUpload--;
                        if (filesToUpload == 0) {
                            app.alert.dismiss('upload');
                            if (callbacks.success) callbacks.success();
                        }
                    },
                    error:function (error) {
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
            if (callbacks.success) callbacks.success();
        }
    };

    app.metadata._patchFields =function(moduleName, module, fields){
        var self = this;
        _.each(fields, function(field, fieldIndex) {

                                    if(field.fields){
                                        field.fields = self._patchFields(moduleName, module, field.fields);
                                        return;
                                    }

                                    var name = _.isString(field) ? field : field.name;
                                    var fieldDef = module.fields[name];
                                    if (!_.isEmpty(fieldDef)) {
                                        // Create a definition if it doesn't exist
                                        if (_.isString(field)) {
                                            field = { name: field };
                                        }

                                        // Flatten out the viewdef, i.e. put 'displayParams' onto the viewdef
                                        // TODO: This should be done on the server-side on my opinion

                                        if (_.isObject(field.displayParams)) {
                                            _.extend(field, field.displayParams);
                                            delete field.displayParams;
                                        }

                                        // Assign type
                                        field.type = field.type || fieldDef.type || "base";
                                        // Patch type

                                        if(self.fieldTypeMap[field.type]){
                                            field.type = self.fieldTypeMap[field.type]
                                        }


                                        // Patch label

                                        field.label = field.label || fieldDef.vname ||  field.name;

                                        fields[fieldIndex] = field;
                                    }
                                    else {
                                        // patch filler string fields to empty base fields of detail view
                                        if (field === "") {
                                            field = {
                                                view:'detail'
                                            };
                                            fields[fieldIndex] = field;
                                        }
                                        // Ignore view fields that don't have module field definition
                                        //app.logger.warn("Field #" + fieldIndex + " '" + name + "' in " + viewName + " view of module " + moduleName + " has no vardef");
                                    }

                                });
        return fields;
    };

    /**
     * Patches view fields' definitions.
     * @param moduleName Module name
     * @param module Module definition
     * @private
     */
    app.metadata._patchMetadata =  function(moduleName, module) {
        if (!module || module._patched === true) return module;
        var self = this;
        _.each(module.views, function(view) {
            if (view.meta) {
                _.each(view.meta.panels, function(panel) {
                    panel.fields = self._patchFields(moduleName, module, panel.fields);
                });
            }
        });
        module._patched = true;
        return module;
    };



    app.view.View = app.view.View.extend({getFieldName :function(panel){
                var self = this;
                var fields = [];
                if(panel.fields){
                    _.each(panel.fields, function(field, fieldIndex){
                       if(field.fields){
                          fields.concat(self.getFieldName(field.fields));
                       }else{
                          fields = fields.concat(_.pluck(panel.fields, 'name'));
                          fields = fields.concat(_.flatten(_.pluck(panel.fields, 'related_fields')));
                       }
                    });
                }
                return fields;


            },

            /**
             * Extracts the field names from the metadata for directly related views/panels.
             * @param {String} module(optional) Module name.
             * @return {Array} List of fields used on this view
             */
        getFieldNames:function(module) {
                var self = this;
                var fields = [];
                module = module || this.context.get('module');

                if (this.meta && this.meta.panels) {
                    _.each(this.meta.panels, self.getFieldName);
                }

                fields = _.compact(_.uniq(fields));

                var fieldMetadata = app.metadata.getModule(module, 'fields');
                if (fieldMetadata) {
                    // Filter out all fields that are not actual bean fields
                    fields = _.reject(fields, function(name) {
                        return _.isUndefined(fieldMetadata[name]);
                    });

                    // we need to find the relates and add the actual id fields
                    var relates = [];
                    _.each(fields, function(name) {
                        if (fieldMetadata[name].type == 'relate') {
                            relates.push(fieldMetadata[name].id_name);
                        }
                    });

                    fields = fields.concat(relates);
                }

                return fields;
            }
    });



})(SUGAR.App);
