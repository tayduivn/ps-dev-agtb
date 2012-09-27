/**
 *
 * Create a modal popup that renders popup layout container
 * @precondition layout metadata must contain a modal layout among the components.
 * array(
 *      'layout' => 'modal',
 *      'showEvent' => [event name] //corresponding trigger name (a single string or array of strings)
 *      ),
 * @trigger [event name] Create popup modal window and draws specified type of layout
 *      @params options - [Object] {
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
                buttons = params.buttons || [],
                message = params.message || '',
                components = (params.components || []),
                title = params.title + '',
                autoResize = params.autoResize || true;
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

            self.context.off("modal:changetitle");
            self.context.on("modal:changetitle", self.changeTitle, self);

            self.show(span,autoResize);
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
                $('<div>', {'class': 'row-fluid'}).append(
                    $('<div>', {'class' : 'modal hide'}).append(
                        this.$body
                    )
                ),
                $("<div>", {'class': 'modal-backdrop hide'})
            );
        }

        if(def.bodyComponent) {
            if(_.isUndefined(this.$body)) {
                this.$body = $('<div>', {'class' : 'modal-body'}).css('overflow-y', 'auto');
                this.$('.modal:first').append(this.$body);
            }
            this.$body.append(comp.el);
        } else {
            this.$('.modal:first').append(comp.el);
        }
    },

    changeTitle: function(title) {
        var header_view = this.getComponent('modal-header');
        if (header_view) {
            header_view.setTitle(title);
            header_view.render();
        }
    },

    open: function(params, callback) {
        this.layout.trigger(this.showEvent, params, callback);
    },

    show: function(span,autoResize) {
        var modal_container = this.$('.modal:first'),
            self = this;

        modal_container.css({ 'margin-top':'-99999px' }).show( 'fast', function() {
            if (_.isNumber(span) && span > 0 && span <= 12) {
                modal_container.addClass('span' + span);
            }

            self.adjustSize(); // adjust modal size

            if (autoResize) {
                // add a watch on the modal height (depends on jquery.watch.js)
                modal_container.watch( 'height', function(){
                    self.adjustSize();
                });
            }
        });
    },

    adjustSize: function() {
        var modal_container = this.$('.modal:first'), // the outer modal div, contains modal-header and modal-body
            modal_body = this.$('.modal-body:first'), // the modal body, contains additional misc content divs, modal-content and modal-footer
            modal_content = this.$('.modal-content:first'), // the main modal-content, adjust size reduces this div
            winHeight = $(window).height() - 100, // reduce allowable window area by top and bottom padding
            self = this;

        modal_body.css({ 'max-height':'none' }); // reset modal-body height to allow for actual size calculation
        modal_content.css({ 'max-height':'none' }); // reset modal-content height to allow for actual size calculation

        var containerHeight = modal_container.outerHeight(), // calculate outer modal height
            bodyHeight = modal_body.outerHeight(), // calculate modal-body height
            contentHeight = modal_content.outerHeight(), // calculate modal-content height
            bodyOffsetHeight = containerHeight - bodyHeight, // height of modal header plus modal footer
            contentOffsetHeight = bodyHeight - contentHeight, // height of additional misc divs above modal-content
            maxBodyHeight = winHeight - bodyOffsetHeight, // calculate maximum modal-body height to prevent view port overflow
            maxContentHeight;

        modal_body.css({ 'max-height':maxBodyHeight, 'overflow':'hidden' });

        if ( containerHeight > winHeight ) { // if the overall modal height was calculated to be larger than the window
            maxContentHeight = maxBodyHeight - contentOffsetHeight; // shorten the modal-content height to fit within the modal-body max height
            modal_content.css({ 'max-height':maxContentHeight, 'overflow':'scroll' });
        }

        modal_container.css({ 'margin-top':-( modal_container.outerHeight() / 2) }).modal('show'); // center modal on window view port
    },

    hide: function(event) {
        var modal_container = this.$('.modal:first');
        modal_container.modal('hide');
    }
})
