/**
 *
 * Create a modal popup that renders popup layout container
 * @precondition layout metadata must contain a modal layout among the components.
 * array(
 *      'layout' => 'modal',
 *      'showEvent' => [event name] //corresponding trigger name (a single string or array of strings)
 *      ),
 * @trigger [event name] Create popup modal window and draws specified type of layout
 *      @params Parameters - [Object] {
 *              span - [int] size of modal[1-12]
 *              options - (Optional) 3rd party options goes here
 *              context - [Object] configured context attributes
 *                        i.e. { module:..., link:..., modelId:... }
 *                        {
 *                            module - [String] Module name (i.e. Accounts, Contacts, etc) (optional),
 *                            link - [String] related module name (optional),
 *                            modelId - [String] model ID (optional)
 *                        }
 *
 *              components - [Array] list of either views or layouts (optional for single layout)
 *                           i.e. [ {view: ... } , {layout: ...}, ...]
 *      }
 *
 *      @params callback - [function(model)] - called by trigger "modal:callback" with correponded model
 *
 * @trigger "modal:callback" Executes binded callback function with the updated model as parameter
 *      @params model - object Backbone model that relates to the current job
 *
 * @trigger "modal:close" Close popup modal and release layout for popup
 *
 * How to Use:
 * in the view widget
 *     this.layout.trigger([event name], ...)
 * in the field widget
 *     this.view.layout.trigger([event name], ...)
 */
({
    components: [
        { 'view' : 'modal-header' }
    ],
    initialize: function(options) {
        var self = this,
            showEvent = options.meta.showEvent;
        options.meta = {
            'components': this.components
        };
        app.view.Layout.prototype.initialize.call(this, options);
        if(_.isArray(showEvent)) {
            //Bind the multiple event handler names
            _.each(showEvent, function(evt, index) {
                if(index == 0) {
                    self.showEvent = evt;
                } else {
                    options.layout.on(evt, function(params, callback){
                        self.open(params, callback);
                    }, self);
                }
            });
        } else {
            self.showEvent = showEvent;
        }
        options.layout.on(this.showEvent, function(params, callback) {
            var span = params.span || '',
                options = params.options || {},
                buttons = params.buttons || [],
                message = params.message || '',
                components = (params.components || []),
                title = params.title + '';
            if(message && components.length == 0) {
                components.push({view: 'modal-confirm', message: message});
            }
            //stops for empty component elements
            if(components.length == 0) return;

            //set title and buttons for modal-header
            var header_view = self.getComponent('modal-header');
            if(header_view) {
                header_view.setTitle(title);
                header_view.setButton(buttons);
            }

            //if previous modal-body exists, remove it.
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

            self.context.off("modal:callback");
            self.context.on("modal:callback", function(model) {
                callback(model);
                self.hide();
            },this);
            self.context.off("modal:close");
            self.context.on("modal:close", self.hide, self);

            self.show({span: span, options: options});
            self.loadData();
            self.render();
        }, this);

    },
    getBodyComponents: function() {
        return _.rest(this._components, this._initComponentSize);
    },
    _placeComponent: function(comp, def) {
        if(this.$('.modal:first').length == 0) {
            //TODO: Replace inline CSS with css property
            this.$el.append(
                $('<div>', {'class' : 'span'}),
                $('<div>', {'class' : 'modal hide'}).append(
                    this.$body
                )
            );
        }

        if(def.bodyComponent) {
            if(_.isUndefined(this.$body)) {
                this.$body = $('<div>', {'class' : 'modal-body'});
                this.$('.modal:first').append(this.$body);
            }
            this.$body.append(comp.el);
        } else {
            this.$('.modal:first').append(comp.el);
        }
    },
    open: function(params, callback) {
        this.layout.trigger(this.showEvent, params, callback);
    },
    show: function(params) {
        var span = params.span,
            modal_container = this.$(".modal:first"),
            //Clean out previous span css class
            original_css = modal_container.attr("class").replace(/span\d+/g, "");
        this._beforeShow(params.options);
        modal_container.attr("class", original_css);
        modal_container.modal(params.options ? params.options.modal : {});
        if(_.isNumber(span) && span > 0 && span <= 12) {
            modal_container.addClass('span' + span);
        }
        modal_container.modal('show');
        this._afterShow();
    },
    hide: function(event) {
        //restore back to the scroll position at the top
        this._beforeHide(event);
        this.$(".modal-body:first").scrollTop(0);
        this.$(".modal:first").modal('hide');
        this._afterHide(event);
    },
    _beforeShow: function(options) {
        //All 3rd party plugin goes here
        return;
    },
    _afterShow: function(options) {
        //All 3rd party plugin goes here
        return;
    },
    _beforeHide: function(event) {
        //All 3rd party plugin goes here
        return;
    },
    _afterHide: function(event) {
        //All 3rd party plugin goes here
        return;
    }
})