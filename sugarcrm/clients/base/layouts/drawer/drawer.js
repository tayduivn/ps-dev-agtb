({
    expandTabHtml: '<div class="drawer-tab"><a href="#" class="btn"><i class="icon-chevron-up"></i></a></div>',
    backdropHtml: "<div class='drawer-backdrop'></div>",

    onCloseCallback: null, //callbacks to be called once drawers are closed

    pixelsFromFooter: 60, //how many pixels from the footer the drawer will drop down to

    initialize: function(options) {
        _.bindAll(this);

        if (!this.$el.is('#drawers')) {
            app.logger.error('Drawer layout can only be included as an Additional Component.');
            return;
        }

        app.drawer = this;
        this.onCloseCallback = [];

        //clear out drawers before routing to another page
        app.routing.before("route", this.reset, this, true);
        app.view.Layout.prototype.initialize.call(this, options);
    },

    /**
     * Open the specified layout in a drawer
     * @param layoutDef
     * @param onClose
     */
    open: function(layoutDef, onClose) {
        var layout;

        //store the callback function to be called later
        if (_.isUndefined(onClose)) {
            this.onCloseCallback.push(function(){});
        } else {
            this.onCloseCallback.push(onClose);
        }

        if (_.isUndefined(layoutDef.context)) {
            layoutDef.context = {};
        }

        if (_.isUndefined(layoutDef.context.forceNew)) {
            layoutDef.context.forceNew = true;
        }

        //initialize layout definition components
        this._addComponentsFromDef([layoutDef]);

        //open the drawer
        this._animateOpenDrawer();

        //load and render new layout in drawer
        layout = _.last(this._components);
        layout.loadData();
        layout.render();
    },

    /**
     * Close the top-most drawer
     * @param any parameters passed into the close method will be passed to the callback
     */
    close: function() {
        var self = this,
            args = Array.prototype.slice.call(arguments, 0);

        if (this._components.length > 0) {
            //close the drawer
            this._animateCloseDrawer(function() {
                self._components.pop().dispose(); //dispose top-most drawer
                (self.onCloseCallback.pop()).apply(this, args); //execute callback
            });
        }
    },

    /**
     * Reload the current drawer with a new layout
     * @param layoutDef
     */
    load: function(layoutDef) {
        var layout = this._components.pop(),
            top = layout.$el.css('top'),
            height = layout.$el.css('height'),
            drawers;

        layout.dispose();
        this._addComponentsFromDef([layoutDef]);

        drawers = this._getDrawers(true);
        drawers.$next
            .addClass('drawer')
            .css({
                top: top,
                height: height
            });

        //refresh tab and backdrop
        this._removeTabAndBackdrop(drawers.$top);
        this._createTabAndBackdrop(drawers.$next, drawers.$top);

        layout = _.last(this._components);
        layout.loadData();
        layout.render();
    },

    /**
     * Retrieves the number of drawers in the stack
     * @returns {Number}
     */
    count: function() {
        return this._components.length;
    },

    /**
     * Remove all drawers and reset
     */
    reset: function() {
        var $main = app.$contentEl.children().first();

        _.each(this._components, function(component) {
            component.dispose();
        }, this);

        this._components = [];
        this.onCloseCallback = [];

        if ($main.hasClass('drawer')) {
            $main
                .removeClass('drawer')
                .css('top','');
            this._removeTabAndBackdrop($main);
        }

        $('body').removeClass('noscroll');
        app.$contentEl.removeClass('noscroll');
    },

    /**
     * Animate opening of a new drawer
     * @private
     */
    _animateOpenDrawer: function() {
        if (this._components.length === 0) {
            return;
        }

        var drawers = this._getDrawers(true),
            drawerHeight = this._determineDrawerHeight();

        if (this._isMainAppContent(drawers.$top)) {
            //make sure that the main application content is set as a drawer
            drawers.$top.addClass('drawer');
            $('body').addClass('noscroll');
            app.$contentEl.addClass('noscroll');
        }

        //add the expand tab and the backdrop to the top drawer
        this._createTabAndBackdrop(drawers.$next, drawers.$top);

        //indicate that it's a drawer
        drawers.$next.addClass('drawer');
        //set the height of the new drawer
        drawers.$next.css('height', drawerHeight);
        //set the animation starting point for the new drawer
        drawers.$next.css('top', drawers.$top.offset().top-drawerHeight);

        //start animation
        _.defer(_.bind(function() {
            if (drawers.$bottom) {
                drawers.$bottom
                    .addClass('transition')
                    .css('top', drawers.$bottom.offset().top + drawerHeight);
            }

            drawers.$top
                .addClass('transition')
                .css('top', this._isMainAppContent(drawers.$top) ? drawerHeight : drawers.$top.offset().top + drawerHeight);

            drawers.$next
                .addClass('transition')
                .css('top','');

            //resize the visible drawer when the browser resizes
            if (this._components.length === 1) {
                $(window).on('resize', this._resizeDrawer);
            }
        }, this));
    },

    /**
     * Animate closing of the top-most drawer
     * @param callback
     * @private
     */
    _animateCloseDrawer: function(callback) {
        if (this._components.length === 0) {
            return;
        }

        var drawers = this._getDrawers(false),
            drawerHeight = this._determineDrawerHeight(),
            transitionEndEvents = 'webkitTransitionEnd oTransitionEnd otransitionend transitionend msTransitionEnd';

        //once the animation is done, reset to original state and execute callback parameter
        drawers.$bottom.one(transitionEndEvents, _.bind(function() {
            drawers.$bottom.off(transitionEndEvents); //some browsers fire multiple transitionend events
            this._removeTabAndBackdrop(drawers.$bottom);
            if (this._isMainAppContent(drawers.$bottom)) {
                drawers.$bottom.removeClass('drawer transition');
                $('body').removeClass('noscroll');
                app.$contentEl.removeClass('noscroll');
            } else {
                //refresh drawer position and height for collapsed or resized drawers
                this._expandDrawer(drawers.$bottom, drawers.$next);
            }
            callback();
        }, this));

        //start the animation to close the drawer
        drawers.$top.css('top', drawers.$top.offset().top-drawerHeight);
        drawers.$bottom.css('top','');
        if (drawers.$next) {
            drawers.$next.css('top', this._isMainAppContent(drawers.$next) ? drawerHeight : drawers.$next.offset().top - drawerHeight);
        }

        //remove resize handler
        if (this._components.length === 1) {
            $(window).off('resize', this._resizeDrawer)
        }
    },

    /**
     * Get all (top, bottom, next) drawers layouts depending upon whether or not a drawer is being opened or closed
     * @param open
     * @return {Object}
     * @private
     */
    _getDrawers: function(open) {
        var $main = app.$contentEl.children().first(),
            $nextDrawer, $topDrawer, $bottomDrawer,
            open = _.isUndefined(open) ? true : open,
            drawerCount = this._components.length;

        switch (drawerCount) {
            case 0: //no drawers
                break;
            case 1: //only one drawer
                $nextDrawer = open ? this._components[drawerCount-1].$el : undefined;
                $topDrawer = open ? $main : this._components[drawerCount-1].$el;
                $bottomDrawer = open? undefined : $main;
                break;
            case 2: //two drawers
                $nextDrawer = open ? this._components[drawerCount-1].$el : $main;
                $topDrawer = open ? this._components[drawerCount-2].$el : this._components[drawerCount-1].$el;
                $bottomDrawer = open? $main : this._components[drawerCount-2].$el;
                break;
            default: //more than two drawers
                $nextDrawer = open ? this._components[drawerCount-1].$el : this._components[drawerCount-3].$el;
                $topDrawer = open ? this._components[drawerCount-2].$el : this._components[drawerCount-1].$el;
                $bottomDrawer = open? this._components[drawerCount-3].$el : this._components[drawerCount-2].$el;
        }

        return {
            $next: $nextDrawer,
            $top: $topDrawer,
            $bottom: $bottomDrawer
        };
    },

    /**
     * Is this drawer the main application content area?
     * @param $layout
     * @return {Boolean}
     * @private
     */
    _isMainAppContent: function($layout) {
        return !$layout.parent().is(this.$el);
    },

    /**
     * Calculate how far down the drawer should drop down, i.e. the height of the drawer
     * @param $mainContent
     * @return {Number}
     * @private
     */
    _determineDrawerHeight: function() {
        var windowHeight = $(window).height(),
            headerHeight = $('#header .navbar').outerHeight(),
            footerHeight = $('footer').outerHeight();

        return windowHeight - headerHeight - footerHeight - this.pixelsFromFooter;
    },

    /**
     * Calculate how much to collapse the drawer
     * @return {Number}
     * @private
     */
    _determineCollapsedHeight: function() {
        return $(window).height()/2; //middle of the window
    },

    /**
     * Create tab and the backdrop. Add the ability to expand and collapse the drawer when the tab is clicked
     * @param $top
     * @param $bottom
     * @private
     */
    _createTabAndBackdrop: function($top, $bottom) {
        //add the expand tab and the backdrop to the top drawer
        $bottom
            .append(this.expandTabHtml)
            .append(this.backdropHtml);

        //add expand/collapse tab behavior
        $bottom.find('.drawer-tab').on('click', _.bind(function(event) {
            if ($('i', event.currentTarget).hasClass('icon-chevron-up')) {
                this._collapseDrawer($top, $bottom);
            } else {
                this._expandDrawer($top, $bottom);
            }
            return false;
        }, this));
    },

    /**
     * Remove the tab and the backdrop and the event listener that handles the ability to expand and collapse the drawer.
     * @param $drawer
     * @private
     */
    _removeTabAndBackdrop: function($drawer) {
        //remove drawer tab
        $drawer.find('.drawer-tab')
            .off('click')
            .remove();

        //remove backdrop
        $drawer.find('.drawer-backdrop')
            .remove();
    },

    /**
     * Expand the drawer
     * @param $top
     * @param $bottom
     * @private
     */
    _expandDrawer: function($top, $bottom) {
        var expandHeight = this._determineDrawerHeight();
        $top.css('height', expandHeight);

        if ($bottom.closest('#drawers').length > 0) {
            $bottom.css('top', expandHeight + $top.offset().top);
        } else {
            $bottom.css('top', expandHeight);
        }

        $bottom
            .find('.drawer-tab i')
            .removeClass('icon-chevron-down')
            .addClass('icon-chevron-up');
    },

    /**
     * Collapse the drawer
     * @param $top
     * @param $bottom
     * @private
     */
    _collapseDrawer: function($top, $bottom) {
        var collapseHeight = this._determineCollapsedHeight();
        $top.css('height', collapseHeight);

        if ($bottom.closest('#drawers').length > 0) {
            $bottom.css('top', collapseHeight + $top.offset().top);
        } else {
            $bottom.css('top', collapseHeight);
        }

        $bottom
            .find('.drawer-tab i')
            .removeClass('icon-chevron-up')
            .addClass('icon-chevron-down');
    },

    /**
     * Test if element is part of active drawer.  Always returns true if there's no inactive components on page.
     * @param el DOM element to test if it is in the active drawer
     * @return Active layout
     */
    isActive: function(el){
        if(_.isEmpty(this._components)){
            return true; // No drawers on page
        }
        var top = this._getDrawers(false).$top;
        return top.find(el).length > 0;
    },

    _dispose: function() {
        app.routing.offBefore("route", this.reset, this);
        this.reset();
        app.view.View.prototype._dispose.call(this);
    },

    /**
     * Resize the height of the drawer by expanding.
     */
    _resizeDrawer: _.throttle(function() {
        var drawers = this._getDrawers(false);
        if (drawers.$top) {
            this._expandDrawer(drawers.$top, drawers.$bottom);
        }
    }, 300)
})

