({
    extendsFrom: 'ModalLayout',

    expandTabHtml: '<div class="drawer-tab"><a href="#" title="Collapse list pane" class="btn edit-expand">'
        + '<i class="icon-chevron-down"></i></a></div>',
    $expandTab: null,

    backdropHtml: "<div class='drawer-squeezed drawer-backdrop'></div>",
    $backdrop: null,

    baseComponents: [], //override modal's baseComponent

    initialize: function(options) {
        app.view.layouts.ModalLayout.prototype.initialize.call(this, options, true);
        this.$el.addClass("drawer");
    },

    _placeComponent: function(component) {
        app.view.Layout.prototype._placeComponent.call(this, component);
    },

    /**
     * Add components to the drawer layout and initialize
     * @param components
     * @private
     */
    _initializeComponents: function(components) {
        //stops for empty component elements
        if(components.length == 0) {
            app.logger.error("Unable to display drawer layout: no components exist");
            return false;
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

        this._addComponentsFromDef(components);
    },

    /**
     * Show/open the drawer
     * @param params
     * @param callback
     * @return {Boolean}
     */
    show: function(params, callback) {
        if (!this.triggerBefore("show")) return false;

        this._setupEventDelegation(params, callback);

        var components = (params.components || this.metaComponents || []);
        if (this._initializeComponents(components) === false) {
            return false;
        }

        this._showDrawer();

        this.loadData();
        this.render();

        this.$el.show();
        this.trigger("show");

        return true;
    },

    /**
     * Hide/close the drawer
     * @return {Boolean}
     */
    hide: function() {
        if (!this.triggerBefore("hide")) return false;

        this.$el.hide();
        this._hideDrawer();
        this.trigger("hide");

        return true;
    },

    _dispose : function(){
        delete this.$backdrop;
        delete this.$expandTab;
    },

    _setupEventDelegation: function(params, callback) {
        if (params.before){
            _.each(params.before, function(callback, event){
                this.offBefore(event);
                this.before(event, callback);
            }, this);
        }

        this.context.off("drawer:callback");
        this.context.on("drawer:callback", function(model) {
            callback(model);
            this.hide();
        }, this);

        this.context.off("drawer:hide");
        this.context.on("drawer:hide", this.hide, this);
    },

    /**
     * Open the drawer
     * @private
     */
    _showDrawer: function() {
        var $existingContent = this.$el.next();
        $existingContent.toggleClass("drawer-squeezed", true);

        if (!this.$backdrop) {
            this.$backdrop = $(this.backdropHtml);
            this.$el.parent().append(this.$backdrop);
        }

        if (!this.$expandTab) {
            this.$expandTab = $(this.expandTabHtml);
            $existingContent.prepend(this.$expandTab);

            // handle drawer tab behavior
            this.$expandTab.on('click', _.bind(function(event) {
                this._toggleDrawer(event.currentTarget);
                return false;
            }, this));
        }

        this.$backdrop.show();
        this.$expandTab.show();
    },

    /**
     * Close the drawer
     * @private
     */
    _hideDrawer: function() {
        this.$el.removeClass('expand');
        this.$el.next().removeClass('collapse drawer-squeezed');
        $('i', this.$expandTab)
            .addClass('icon-chevron-down')
            .removeClass('icon-chevron-up');

        this.$expandTab.hide();

        this.$backdrop.removeClass('collapse');
        this.$backdrop.hide();
    },

    /**
     * Toggle the drawer
     * @param target
     * @private
     */
    _toggleDrawer: function(target) {
        $('i', target)
            .toggleClass('icon-chevron-up')
            .toggleClass('icon-chevron-down');
        this.$el.toggleClass('expand');
        this.$backdrop.toggleClass('collapse');
        this.$el.next().toggleClass('collapse');
    }
})