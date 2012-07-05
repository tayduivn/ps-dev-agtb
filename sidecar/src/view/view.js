(function(app) {

    /**
     * Base View class. Use {@link View.ViewManager} to create instances of views.
     *
     * @class View.View
     * @alias SUGAR.App.view.View
     */
    app.view.View = app.view.Component.extend({

        /**
         * TODO: add docs (describe options parameter, see Component class for an example).
         * @constructor
         * @param options
         */
        initialize: function(options) {
            app.view.Component.prototype.initialize.call(this, options);

            /**
             * Name of the view (required).
             * @cfg {String}
             */
            this.name = options.name;

            /**
             * Template to render (optional).
             * @cfg {Function}
             */
            this.template = options.template || app.template.getView(this.name, this.module) ||
                            app.template.getView(this.name) ||
                            (options.meta && options.meta.type ? app.template.getView(options.meta.type) : null);

            /**
             * Dictionary of field widgets.
             *
             * - keys: field IDs (sfuuid)
             * - value: instances of `app.view.Field` class
             */
            this.fields = {};

            /**
             * A template to use for view fields if a field does not have a template defined for its parent view.
             * Defaults to `"default"`.
             *
             * For example, if you have a subview and don't want to define subview template for all field types,
             * you may choose to use existing templates like `detail` if your subview is in fact a detail view.
             *
             * @property {String}
             */
            this.fallbackFieldTemplate = "default";

            /**
             * Reference to the parent layout instance.
             * @property {View.Layout}
             */
            this.layout = this.options.layout;

            var defaultValue = {};
            defaultValue[this.module] = this.module;

            /**
             * Singular i18n-ed module name.
             * @property {String}
             * @member View.View
             */
            this.moduleSingular = app.lang.getAppListStrings("moduleListSingular", defaultValue)[this.module];

            /**
             * Pluralized i18n-ed module name.
             * @property {String}
             * @member View.View
             */
            this.modulePlural = app.lang.getAppListStrings("moduleList", defaultValue)[this.module];

            // Used only for debugging
            if (app.config.env == "dev") this.$el.data("comp", "view_" + this.name);
        },

        /**
         * Sets template option.
         *
         * If the given option already exists it is augmented by the value of the given `option` parameter.
         * See Handlebars.js documentation for details.
         * @param {String} key Option key.
         * @param {Object} option Option value.
         */
        setTemplateOption: function(key, option) {
            this.options = this.options || {};
            this.options.templateOptions = this.options.templateOptions || {};
            this.options.templateOptions[key] = _.extend({}, this.options.templateOptions[key], option);
        },

        /**
         * Renders a view onto the page.
         *
         * This method uses `ctx` parameter as the context for the view's Handlebars {@link View.View#template}
         * and view's `options.templateOptions` property as template options.
         *
         * If no `ctx` parameter is specified, `this` is passed as the context for the template.
         * If no `options` parameter is specified, `this.options.templateOptions` is used.
         *
         * You can override this method if you have custom rendering logic and don't use Handlebars templating
         * or if you need to pass different context object for the template.
         *
         * Example:
         * <pre><code>
         * app.view.views.CustomView = app.view.View.extend({
         *    _renderSelf: function() {
         *       var ctx = {
         *         // Your custom context for this view template
         *       };
         *       app.view.View.prototype._renderSelf.call(this, ctx);
         *    }
         * });
         *
         * // Or totally different logic that doesn't use this.template
         * app.view.views.AnotherCustomView = app.view.View.extend({
         *    _renderSelf: function() {
         *       // Never do this :)
         *       return "&lt;div&gt;Hello, world!&lt;/div&gt;";
         *    }
         * });
         *
         *
         * </code></pre>
         *
         * This method uses this view's {@link View.View#template} property to render itself.
         * @param ctx(optional) Template context.
         * @param options(optional) Template options.
         * <pre><code>
         * {
         *    helpers: helpers,
         *    partials: partials,
         *    data: data
         * }
         * </code></pre>
         * See Handlebars.js documentation for details.
         * @protected
         */
        _renderSelf: function(ctx, options) {
            if (this.template) {
                try {
                    this.$el.html(this.template(ctx || this, options || this.options.templateOptions));
                    // See the following resources
                    // https://github.com/documentcloud/backbone/issues/310
                    // http://tbranyen.com/post/missing-jquery-events-while-rendering
                    // http://stackoverflow.com/questions/5125958/backbone-js-views-delegateevents-do-not-get-bound-sometimes
                    this.delegateEvents();
                } catch (e) {
                    app.logger.error("Failed to render " + this + "\n" + e);
                    // TODO: trigger app event to render an error message
                }
            }
        },

        /**
         * Renders a field.
         *
         * This method sets field's view element and invokes render on the given field.
         * @param {View.Field} field The field to render
         * @protected
         */
        _renderField: function(field) {
            field.setElement(this.$("span[sfuuid='" + field.sfId + "']"));
            try {
                field.render();
            } catch (e) {
                app.logger.error("Failed to render " + field + " on " + this + "\n" + e);
                // TODO: trigger app event to render an error message
            }
        },

        /**
         * Renders a view onto the page.
         *
         * The method first renders this view by calling {@link View.View#_renderSelf}
         * and then for each field invokes {@link View.View#_renderField}.
         *
         * NOTE: Do not override this method, otherwise you will loose ACL check.
         * Consider overriding {@link View.View#_renderSelf} instead.
         *
         * @return {Object} Reference to this view.
         * @private
         */
        _render: function() {
            if (app.acl.hasAccessToModel(this.name, this.model)) {
                _.each(this.fields, function(field) {
                    field.dispose();
                });
                this.fields = {};

                this._renderSelf();
                // Render will create a placeholder for sugar fields. we now need to populate those fields
                _.each(this.fields, function(field) {
                    this._renderField(field);
                }, this);
            } else {
                app.logger.info("Current user does not have access to this module view.");
                //TODO trigger app event to notify user about no access or render a "no access" template
            }

            return this;
        },

        /**
         * Fetches data for view's model or collection.
         *
         * This method calls view's context {@link Core.Context#loadData} method
         * and sets context's `fields` property beforehand.
         *
         * Override this method to provide custom fetch algorithm.
         */
        loadData: function() {
            this.context.set("fields", this.getFieldNames());
            this.context.loadData();
        },

        /**
         * Extracts the field names from the metadata for directly related views/panels.
         * @param {String} module(optional) Module name.
         * @return {Array} List of fields used on this view
         */
        getFieldNames: function(module) {
            var fields = [];
            module = module || this.context.get('module');

            if (this.meta && this.meta.panels) {
                _.each(this.meta.panels, function(panel) {
                    fields = fields.concat(_.pluck(panel.fields, 'name'));
                });
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
        },

        /**
         * Gets a hash of fields that are currently displayed on this view.
         *
         * The hash has field names as keys and field definitions as values.
         * @param {String} module(optional) Module name.
         * @return {Object} The currently displayed fields.
         */
        getFields: function(module) {
            var fields = {};
            var fieldNames = this.getFieldNames(module);
            _.each(this.fields, function(field) {
                if (_.include(fieldNames, field.name)) {
                    fields[field.name] = field.def;
                }
            });
            return fields;
        },

        /**
         * Returns a field by name.
         * @param {String} name Field name.
         * @return {View.Field} Instance of the field widget.
         */
        getField: function(name) {
            return _.find(this.fields, function(field) {
                return field.name == name;
            });
        },

        /**
         * Binds data changes to the model to trigger an initial view to render
         */
        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
            }
        },

        /*
         * Disposes a view.
         *
         * This method disposes view fields and calls
         * {@link View.Component#_dispose} method of the base class.
         * @protected
         */
        _dispose: function() {
            _.each(this.fields, function(field) {
                field.dispose();
            });
            this.fields = {};
            app.view.Component.prototype._dispose.call(this);
        },

        /**
         * Gets a string representation of this view.
         * @return {String} String representation of this view.
         */
        toString: function() {
            return "view-" + this.name + "-" + app.view.Component.prototype.toString.call(this);
        }

    });


})(SUGAR.App);
