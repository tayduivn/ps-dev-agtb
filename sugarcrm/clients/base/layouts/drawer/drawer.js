/**
 * Create a drawer view (optionally modal)
 * Incomplete
 */
({
    initialize: function(options) {
        var self = this,
            showEvent = options.meta.showEvent;

        options.meta = _.clone(options.meta);
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
        var params = params || {},
            message = params.message || '',
            components = (params.components || this.metaComponents || []),
            title = (params.title || this.meta.title) + '';

        //stops for empty component elements
        if(components.length == 0) {
            app.logger.error("Unable to display modal dialog: no components or message");
            return false;
        }

        //Push down the existing content
        this.existingContent = this.existingContent || $("#content>div>div:first");
        if (!this.visible) {
            this.existingContent.addClass("drawer-squeezed");
        }


        //if previous modal-body exists, remove it.
        if(!_.isUndefined(this._initComponentSize)) {
            for(var i = 0; i < this._components.length; i++) {
                this._components[this._components.length - 1].$el.remove();
                this.removeComponent(this._components.length - 1);
            }
        } else {
            //attach the el above all other content
            this.$el.insertBefore("#content>div>div:first");
            this._initComponentSize = this._components.length;
        }

        this.backdrop = this.backdrop || $("<div class='drawer-squeezed drawer-backdrop'></div>").insertAfter(this.existingContent);
        this.expandTab = this.expandTab || $('<div class="drawer-tab"><a href="#" title="Collapse list pane" class="btn edit-expand">'
            + '<i class="icon-chevron-down"></i></a></div>').insertBefore(this.existingContent.children().first());

        this.backdrop.css("display","block");
        this.expandTab.css("display","block");

        this.expandTab.off();
        var self = this;
        this.expandTab.on('click', function () {
            $(this).find('i').toggleClass('icon-chevron-up').toggleClass('icon-chevron-down');
            self.$el.toggleClass('expand');
            self.backdrop.toggleClass('collapse');
            self.existingContent.toggleClass('collapse');
          return false;
        });

        this._addComponentsFromDef(components);

        this.context.off("drawer:callback");
        this.context.on("drawer:callback", function(model) {
            callback(model);
            this.hide();
        },this);
        this.context.off("drawer:hide");
        this.context.on("drawer:hide", this.hide, this);


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

        //Clean out previous span css class
        this.$el.show();

        this.visible = true;
        this.trigger("show");
        return true;
    },
    hide: function(event) {
        if (!this.triggerBefore("hide")) return false;
        this.$el.hide();
        this.$el.removeClass('expand');
        this.backdrop.removeClass('collapse');
        this.existingContent.removeClass('collapse');
        this.visible = false;
        this.backdrop.css("display", "none");
        this.expandTab.css("display", "none");
        this.existingContent.removeClass("drawer-squeezed");
        this.trigger("hide");
        return true;
    },

    _dispose : function(){
        delete this.existingContent;
        delete this.backdrop;
        delete this.expandTab;
        app.view.layouts.ModalLayout.prototype._dispose.call(this);
    }
})