(function(app) {
    var sfid = 0;

    //Register Handlebars helper to create fields with unique id's
    Handlebars.registerHelper('sugar_field', function(context, view, bean) {
        var ret = '<span sfuuid="' + (++sfid) + '"></span>',
            name = this.name,
            label = this.label || this.name,
            def = this,
            sf;

        bean = bean || context.get("model");

        if (bean.fields && bean.fields[name]) {
            def = bean.fields[name];
        }

        sf = view.sugarFields[sfid] || (view.sugarFields[sfid] = app.sugarFieldManager.get({
            def: def,
            view: view,
            context: context,
            label: label,
            model: bean || context.get("model")
        }));

        sf.sfid = sfid;

        return new Handlebars.SafeString(ret);
    });

    /**
     * SugarField widget. A sugarfield widget is a low level field widget. Some examples of sugarfields are
     * text boxes, date pickers, drop down menus.
     *
     * ##Creating a SugarField
     * SugarCRM allows for customized "sugarfields" which are visual representations of a type of data (e.g. url would
     * be displayed as a hyperlink).
     *
     * ###Anatomy of a SugarField
     * Sugarfield files resides in the **`sugarcrm/include/SugarFields/{{SUGARFIELD_NAME}}`** folder.
     *
     * Inside the {{SUGARFIELD_NAME}} directory are different folders of the SugarField which correspond to their respective supported
     * views. A typical directory structure will look like the following:
     * <pre>
     * SugarField
     * |- portal
     *   |-default.js
     *   |-sugarField.js
     *   |-editView.hbt
     *   |-detailView.hbt
     * |- mobile
     *   |-sugarField.js
     *   |-editView.hbt
     * |- base
     *   |-sugarField.js
     *   |-listFiew.hbt
     * </pre>
     * **`sugarField.js`** contains the controller for your SugarField; this includes event handlers and necessary data
     * bindings.
     * <pre><code>
     * var controller = {
     *    events: {
     *        handler: function() {}
     *    }
     * }
     * </code></pre>
     *
     * **`viewName.hbt`** contains your templates corresponding to the type of view the SugarField is to be displayed on.
     * Sugar uses Handlebars.js as its client side template of choice. At this time no other templating engines are
     * supported. Sample:
     * <pre><code>
     * &lt;span name="{{name}}"&gt;{{value}}&lt;/span&gt;
     * </code></pre>
     *
     * These files will be used by the metadata manager to generate metadata for your SugarFields and pass them onto the
     * Sugar JavaScript client.
     *
     * ###Advanced SugarFields
     * Sometimes a SugarField needs to do more than just display a simple input element, other times input elements
     * additional data such as drop down menu choices. To support advanced functionality, just add your additional
     * controller logic to **`sugarField.js`** javascript file where sugarfield is the name of the SugarField.
     * <pre><code>
     * ({
     *     events: {
     *         handler: function() {
     *             // Actions
     *         }
     *     },
     *
     *     initialize: function() {
     *        this.parent.initialize();
     *     },
     *
     *     format: function() {
     *         return // Some formatted option;
     *     }
     * })
     * </pre></code>
     *
     * @class SugarField
     */
    app.augment('sugarField', {
        base : Backbone.View.extend({
            /**
             * Reference to the application
             * @property {Object}
             */
            app: app,

            /**
             * Reference to the parent constructor
             * @property {Object}
             */
            parent: this,

            /**
             * Id of the SugarField
             * TODO: This is a shared property on the SugarField
             * @property {Number}
             */
            sfid: -1,

            initialize: function(options) {
                var templateKey;
                _.extend(this, options.def);

                this.view = options.view;
                this.label = options.label;
                this.bindModelChange(options.context, options.model || options.context.get("model"));
                this.viewName = this.view.name;
                this.meta = app.metadata.get({sugarField:this});

            // this is experimental to try to see if we can have custom events on sugarfields themselves.
            // the following line doesn't work, need to _.extend it or something.
            // this.events = this.meta.events;
            templateKey = "sugarField." + this.name + "." + this.view.name;

                this.templateC = app.template.get(templateKey) || app.template.compile(this.meta.template, templateKey);
            },

            /**
             * Override default Backbone.Events to also use custom handlers
             * TODO: Convert string function names to references to the callback function
             * The events hash is similar to the backbone events. We store the eventHandlers as
             * part of the SugarField with the `"callback_"` prefix.
             * <pre><code>
             * events: {
             *     handler: "function() {}";
             * }
             * </code></pre>
             * Is stored as:
             * <pre><code>
             * this.callback_handler
             * </code></pre>
             * @private
             * @param {Object} events Hash of events and their handlers
             */
            delegateEvents : function(events) {
                if (!(events || (events = this.events))) {
                    return;
                }

                events = _.clone(events);

                _.each(events, function(eventHandler, handlerName) {
                    var callback = this[eventHandler];

                    // If our callbacks / events have not been registered, go ahead and registered.
                    if (!callback && _.isString(eventHandler)) {
                        try {
                            callback = eval("(" + eventHandler + ")");

                            // Store this callback if it is a function. Prefix it with "callback_"
                            if (_.isFunction(callback)) {
                                this["callback_" + handlerName] = callback;
                                events[handlerName] = "callback_" + handlerName;
                            }
                        } catch(e) {
                            app.logger.error("invalid event callback " + handlerName + " : " + eventHandler);
                            delete events[handlerName];
                        }
                    }

                }, this);

                Backbone.View.prototype.delegateEvents.call(this, events);
            },

            /**
             * Renders the SugarField widget
             * @method
             * @return {Object} this Reference to the SugarField
             */
            render: function() {
                // If we don't have any data in the model yet
                if (!(this.model instanceof Backbone.Model)) {
                    return null;
                }

                this.value = this.model.has(this.name) ? this.model.get(this.name) : "";
                this.$el.html(this.templateC(this));

                var model = this.model;
                var field = this.name;

                this.bindDomChange(model, field);

                return this;
            },

            /**
             * Binds DOM changes to set field value on model
             * @param {Object} model backbone model
             * @param {String} fieldName
             */
            bindDomChange: function(model, fieldName) {
                var self = this;
                var el = this.$el.find("input");
                // Bind input to the model
                el.on("change", function(ev) {
                    model.set(fieldName, self.unformat(el.val()));
                });
            },

            /**
             * Formats values for display
             * This function is meant to be overridden by a sugarFieldname.js controller class
             * @param {Mixed} value
             * @return {Mixed}
             */
            format: function(value) {
                return value;
            },

            /**
             * Unformats values for display
             * This function is meant to be overridden by a sugarFieldname.js controller class
             * @param {Mixed} value
             * @return {Mixed}
             */
            unformat: function(value) {
                return value;
            },

            /**
             * Binds render to model changes
             * @param {Context} context
             * @param {Bean} model Data to bind the sugarfield to
             */
            bindModelChange: function(context, model) {
                this.unBind();
                this.context = context;
                this.model = model;

                if (this.model.on){
                    this.model.on("change:" + this.name, this.render, this);
                }
            },

            /**
             * Unbinds model event callbacks
             * @method
             */
            unBind: function() {
                //this will only work if all events we listen to, we set the scope to this
                if (this.model && this.model.offByScope) {
                    this.model.offByScope(this);
                }

                delete this.model;
                delete this.context;
            }
        })
    });
}(SUGAR.App));