/**
 *
 * Create a modal popup that renders popup layout container
 * @precondition layout metadata must contain a modal layout among the components.
 * array(
 *      'layout' => 'modal',
 *      'showEvent' => [event name] //corresponding trigger name
 *      ),
 * @trigger [event name] Create popup modal window and draws specified type of layout
 *      @params module - String Module name (i.e. Accounts, Contacts, etc)
 *      @params layout - Sugar Layout
 *      @params callback - function(model) - called by trigger "modal:callback" with correponded model
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
    initialize: function(options) {
        var self = this,
            showEvent = options.meta.showEvent;
        options.meta = {
            'components': [
                { 'view' : 'modal-header' },
                { 'view' : 'modal-footer' }
            ]
        };
        app.view.Layout.prototype.initialize.call(this, options);
        if(_.isArray(showEvent)) {
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
        options.layout.on(self.showEvent, function(params, callback) {
            var module = params.module || '',
                layout = params.layout,
                span = params.span || '',
                modelId = params.modelId || '',
                title = params.title + '';
            //if container defined
            if(_.isUndefined(layout))
                return;
            self.context.set("title", title);

            //if previous popup-body exists, remove it.
            var popup = self.getPopupComponent();
            if(popup) {
                popup.$el.remove();
                self.removeComponent(self.mId);
            } else {
                self.mId = self._components.length;
            }
            self.show(span);
            //set default parameters and initialize context
            var context = app.context.getContext();
            context.set({
                name: layout,
                layout:layout,
                module: module,
                modelId: modelId
            }).prepare();

            self.addComponent(app.view.createLayout({
                name: layout,
                module: module,
                context: context
            }), { 'layout' : layout });
            self.getPopupComponent().off("modal:callback");
            self.getPopupComponent().on("modal:callback", function(model) {
                callback(model);
                self.hide();
            },this);
            self.getPopupComponent().off("modal:close");
            self.getPopupComponent().on("modal:close", self.hide,this);

            self.loadData();
            self.render();
        }, this);

        //For global handler
        options.context.off("modal:open", null, this);
        options.context.on("modal:open", function(params, callback) {
            options.layout.trigger(showEvent, params, callback);
        }, this);
    },
    getPopupComponent: function() {
        return _.isNumber(this.mId) ? this._components[this.mId] : null;
    },
    _placeComponent: function(comp, def) {
        if(this.$('.modal:first').length == 0) {
            //TODO: Replace inline CSS with css property
            this.$el.append(
                $('<div>', {'class': 'row-fluid'}).append(
                    $('<div>', {'class' : 'modal hide'}).append(
                        $('<div>', {'class' : 'modal-header'}),
                        $('<div>', {'class' : 'modal-body'}).css('overflow-y', 'auto'),
                        $('<div>', {'class' : 'modal-footer'})
                    )
                ),
                $("<div>", {'class': 'modal-backdrop hide'})
            );
        }

        if(def.view) {
            this.$('.' + def.view + ':first').replaceWith(comp.el);
        } else {
            this.$('.modal-body:first').append(comp.el);
        }
    },
    open: function(params, callback) {
        this.layout.trigger(this.showEvent, params, callback);
    },
    show: function(span) {
        var modal_container = this.$(".modal:first"),
            maxHeight = $(window).height() - ($(".modal-header:first").outerHeight() * 2) - 200;

        //TODO: Replace inline CSS with css property
        this.$el.addClass("modal-open");
        this.$el.children(".modal-backdrop").show();
        modal_container.attr({
            style: "",
            class: "modal"
        }).show();

        if(_.isNumber(span) && span > 0 && span <= 12) {
            modal_container.addClass('span' + span).css({
                'margin' : '0',
                'left' : (4.255 * (12 - span)) + '%',
                'top' : '5%',
                'max-height' : 'none'
            });
            modal_container.children(".modal-body").css({
                'padding': '0',
                'max-height' : maxHeight
            });
        } else {
            modal_container.css({
                'margin-top' : '0',
                'margin-bottom' : '0',
                'top' : '5%',
                'max-height' : 'none'
            });

            modal_container.children(".modal-body").css({
                'padding': '',
                'max-height' : maxHeight
            });
        }
    },
    hide: function(event) {
        //restore back to the scroll position at the top
        this.$(".modal-body:first").scrollTop(0);
        this.$el.removeClass("modal-open");
        this.$(".modal:first").hide();
        this.$el.children(".modal-backdrop").hide();
    }
})