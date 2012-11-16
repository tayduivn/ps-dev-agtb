/**
 * Create a drawer view (optionally modal)
 * Incomplete
 */
({
    initialize: function(options) {
        var self = this,
            showEvent = options.meta.showEvent;

        if(!_.isFunction(this.$el.modal)) {
            app.logger.error("Unable to load modal.js: Needs bootstrap modal plugin.");
        }

        this.metaComponents = options.meta.components;
        options.meta.components = [];
        if (options.meta.before){
            _.each(options.meta.before, function(callback, event){
                self.before(event, callback);
            });
        }
        app.view.Layout.prototype.initialize.call(this, options);
        this.$el.addClass("drawer");
        if (showEvent)
        {
            if(_.isArray(showEvent)) {
                //Bind the multiple event handler names
                _.each(showEvent, function(evt, index) {
                    self._bindShowEvent(evt);
                });
            } else {
                self._bindShowEvent(showEvent);
            }
        }
    },

    _bindShowEvent : function(event, delegate){
        var self = this;
        if (_.isObject(event))
        {
            delegate = event.delegate;
            event = event.event;
        }
        if (delegate){
            self.layout.events = self.layout.events || {};
            self.layout.events[event] = function(params, callback){self.show(params, callback)};
            self.layout.delegateEvents();
        } else {
            self.layout.on(event, function(params, callback){self.show(params, callback);}, self);
        }
    },

    /**
     *
     * @param params
     * @param callback
     * @private
     */
    _buildComponentsBeforeShow : function(params, callback) {
        var self = this,
            params = params || {},
            buttons = params.buttons || [],
            message = params.message || '',
            components = (params.components || this.metaComponents || []),
            title = (params.title || this.meta.title) + '';

        //stops for empty component elements
        if(components.length == 0) {
            app.logger.error("Unable to display modal dialog: no components or message");
            return false;
        }

        //Push down the existing content

        if (!this.visible) {
            $("#content>div>div:first").addClass("drawer-squeezed");
        }


        //if previous modal-body exists, remove it.
        if(_.isUndefined(self._initComponentSize)) {
            //attach the el above all other content
            this.$el.insertBefore("#content>div>div:first");
            $("<div class='edit-backdrop'></div>").insertAfter(this.$el);
            for(var i = 0; i < self._components.length; i++) {
                self._components[self._components.length - 1].$el.remove();
                self.removeComponent(self._components.length - 1);
            }
        } else {
            self._initComponentSize = self._components.length;
        }
        _.each(components, function(def) {
            def = _.extend(def, {bodyComponent: true});
            var context = self.context,
                module = self.context.get('module');

            if(params.context) {
                if(params.context.link) {
                    context = self.context.getChildContext(params.context);
                } else {
                    context = app.context.getContext(params.context);
                    context.parent = self.context;
                }
                context.prepare();
                module = context.get("module");
            }
            if (def.view) {
                self.addComponent(app.view.createView({
                    context: context,
                    name: def.view,
                    message: def.message,
                    module: module,
                    layout: self
                }), def);
            }
            else if(def.layout) {
                self.addComponent(app.view.createLayout({
                    name: def.layout,
                    module: module,
                    context: context
                }), def);
            }
        });

        self.context.off("modal:callback");
        self.context.on("modal:callback", function(model) {
            callback(model);
            self.hide();
        },self);
        self.context.off("modal:close");
        self.context.on("modal:close", self.hide, self);


    },

    show: function(params, callback) {
        if (!this.triggerBefore("show")) return false;
        var self = this;
        if (params.before){
            _.each(params.before, function(callback, event){
                self.offBefore(event);
                self.before(event, callback);
            });
        }

        if (this._buildComponentsBeforeShow(params, callback) === false)
            return false;
        this.loadData();
        this.render();
        var options = params ? params.options || {} : {};

        //Clean out previous span css class
        this.$el.show();

        this.visible = true;
        this.trigger("show");
        return true;
    },
    hide: function(event) {
        if (!this.triggerBefore("hide")) return false;
        this.$el.hide();
        this.trigger("hide");
        this.visible = false;
        return true;
    }
})