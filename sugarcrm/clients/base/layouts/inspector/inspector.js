/**
 * Inspector Layout, this is primarily used to display more details about a given row in a table.
 *
 * @trigger [event name] Create inspector window and draws specified type of components
 *      @params Parameters - [Object] {
 *              options - (Optional)
 *              events - [Object] - List of events to bind to this model
 *              components - [Array] list of either views or layouts (optional for single layout)
 *                           i.e. [ {view: ... } , {layout: ...}, ...]
 *      }
 *
 * Events:
 * @trigger "beforeShow" [layout, rowIndex] Before the show logic is called, it is called with "this" object and the
 *                          the current rowIndex that is being selected
 * @trigger "show" [layout, rowIndex] After the show logic is called, it is called with "this" object and the
 *                          the current rowIndex that is being selected
 * @trigger "beforeHide" [layout] Before the hide logic is called, it is called with "this" object
 * @trigger "hide" [layout] After the hide logic is called, it is called with "this" object
 * @trigger "next" [layout] Fired from the Inspector Header, it allows you to trigger a next event to move to a next
 *                          item if one exists
 * @trigger "previous" [layout] Fired from the Inspector Header, it allows you to trigger a previous event to move to a
 *                          previous item if one exists
 * @trigger "inspector:close" Close the inspector window and clean up all the events
 *
 */
({
    baseComponents: [
        { 'view' : 'inspector-header' }
    ],
    initialize: function(options) {
        var self = this,
            showEvent = options.meta.showEvent;

        this.metaComponents = options.meta.components;
        options.meta.components = this.baseComponents;
        app.view.Layout.prototype.initialize.call(this, options);
        if(_.isArray(showEvent)) {
            //Bind the multiple event handler names
            _.each(showEvent, function(evt, index) {
                self._bindShowEvent(evt);
            });
        } else {
            self._bindShowEvent(showEvent);
        }
    },
    _bindShowEvent : function(event, delegate){
        var self = this;
        if (_.isObject(event)) {
            delegate = event.delegate;
            event = event.event;
        }
        if (delegate){
            self.layout.events = self.layout.events || {};
            self.layout.events[event] = function(params){self.display(params)};
            self.layout.delegateEvents();
        } else {
            self.layout.on(event, function(params){self.display(params);}, self);
        }
    },
    getBodyComponents: function() {
        return _.rest(this._components, this._initComponentSize);
    },
    _placeComponent: function(comp, def) {
        if(this.$('.inspector:first').length == 0) {
            this.$el.append(
                $('<div>', {'class' : 'inspector hide'}).append(
                    this.$body
                )
            );
        }

        if(def.bodyComponent) {
            if(_.isUndefined(this.$body)) {
                this.$body = $('<div>', {'class' : 'inspector-body'});
                this.$('.inspector:first').append(this.$body);
            }
            this.$body.append(comp.el);
        } else {
            this.$('.inspector:first').append(comp.el);
        }
    },

    /**
     *
     * @param params
     * @private
     */
    _buildComponentsBeforeShow : function(params) {
        var self = this,
            params = params || {},
            components = (params.components || this.metaComponents || []),
            title = (params.title || this.meta.title) + '';

        //set title and buttons for inspector-header
        var header_view = self.getComponent('inspector-header');
        if(header_view) {
            header_view.setTitle(title);
        }

        //if previous inspector-body exists, remove it.
        if(self._initComponentSize) {
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
    },

    isVisible: function() {
        return this.$(".inspector:first").hasClass('show');
    },

    display: function(params) {
        var self = this,
            rowIndex = params.rowIndex;

        // if it's not currently visible, init the events and build out the components
        if(!this.isVisible()) {
            if (params.events){
                if(params.events.before) {
                    _.each(params.events.before, function(callback, event){
                        if(_.isFunction(callback)) {
                            self.offBefore(event);
                            self.before(event, callback);
                        }
                    });
                    delete params.events.before;
                }

                _.each(params.events, function(callback, event){
                    if(_.isFunction(callback)) {
                        self.off(event);
                        self.on(event, callback);
                    }
                });
            }
            // register the global close context
            self.context.off("inspector:close");
            self.context.on("inspector:close", self.hide, self);
        }

        if (this._buildComponentsBeforeShow(params) === false) {
            return false;
        }

        // actually show the inspector window
        this.show(rowIndex);

        return true;
    },

    show: function(rowIndex) {
        if (!this.triggerBefore("show", this, rowIndex)) return false;

        this.loadData();
        this.render();

        var inspector_container = this.$(".inspector:first");
        inspector_container.removeClass('hide').addClass('show');

        this.trigger("show", this, rowIndex);
        return true;
    },
    hide: function() {
        if (!this.isVisible()) return false;

        if (!this.triggerBefore("hide", this)) return false;
        //restore back to the scroll position at the top
        var inspector_container = this.$(".inspector:first");
        inspector_container.scrollTop(0).removeClass('show').addClass('hide');

        this.trigger("hide", this);

        // Clean up any events left
        this.off();
        this.offBefore();
        return true;
    }
})