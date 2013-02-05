({
    expandTabHtml: '<div class="drawer-tab"><a href="#" class="btn"><i class="icon-chevron-up"></i></a></div>',
    backdropHtml: "<div class='drawer-backdrop'></div>",

    onCloseCallback: null, //callbacks to be called once drawers are closed

    initialize: function(options) {
        if (!this.$el.is('#drawers')) {
            app.logger.error('Drawer layout can only be included as an Additional Component.');
            return;
        }

        app.drawer = this;
        this.onCloseCallback = [];

        //clear out drawers before routing to another page
        var before = app.routing.before;
        app.routing.before = _.bind(function(route, args) {
            this.reset();
            return before(route, args);
        }, this);

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

        //initialize layout definition components
        this._addComponentsFromDef([layoutDef]);

        //open the drawer
        this._animateOpenDrawer();

        //load and render new layout in drawer
        layout = this._components[this._components.length-1];
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
        var layout = this._components[this._components.length-1];

        this._components.pop().dispose();
        this._addComponentsFromDef([layoutDef]);

        layout.loadData();
        layout.render();
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
            $main.find('.drawer-tab').remove(); //remove drawer tab
            $main.find('.drawer-backdrop').remove(); //remove backdrop
        }
    },

    /**
     * Animate opening of a new drawer
     * @private
     */
    _animateOpenDrawer: function() {
        if (this._components.length === 0) {
            return;
        }

        var $main = app.$contentEl.children().first(),
            $newDrawer = this._components[this._components.length-1].$el,
            $topDrawer,
            $bottomDrawer,
            drawerHeight = this._determineDrawerHeight();

        //identify top and bottom drawers
        if (this._components.length === 1) {
            $topDrawer = $main;
            $topDrawer.addClass('drawer');
        } else if (this._components.length === 2) {
            $topDrawer = this._components[this._components.length-2].$el;
            $bottomDrawer = $main;
            this._expandDrawer($topDrawer, $bottomDrawer); //expand current drawer if collapsed
        } else {
            $topDrawer = this._components[this._components.length-2].$el;
            $bottomDrawer = this._components[this._components.length-3].$el;
            this._expandDrawer($topDrawer, $bottomDrawer); //expand current drawer if collapsed
        }

        //add the expand tab and the backdrop to the top drawer
        $topDrawer
            .append(this.expandTabHtml)
            .append(this.backdropHtml);

        //add expand/collapse tab behavior
        this._handleTabBehavior($newDrawer, $topDrawer);

        //set the height of the new drawer
        $newDrawer.css('height', drawerHeight);
        //set the animation starting point for the new drawer
        $newDrawer.css('top', $topDrawer.offset().top-drawerHeight);

        //start animation
        _.defer(_.bind(function() {
            if ($bottomDrawer) {
                $bottomDrawer.css('top', $bottomDrawer.offset().top + drawerHeight);
            }
            $topDrawer.css('top', this._components.length === 1 ? drawerHeight : $topDrawer.offset().top + drawerHeight);
            $newDrawer
                .addClass('drawer')
                .css('top','');
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

        var $main = app.$contentEl.children().first(),
            $topDrawer = this._components[this._components.length-1].$el,
            $bottomDrawer,
            $hiddenDrawer,
            drawerHeight = this._determineDrawerHeight();

        //identify bottom and hidden drawers
        if (this._components.length === 1) {
            $bottomDrawer = $main;
        } else if (this._components.length === 2) {
            $bottomDrawer = this._components[this._components.length-2].$el;
            $hiddenDrawer = $main;
        } else {
            $bottomDrawer = this._components[this._components.length-2].$el;
            $hiddenDrawer = this._components[this._components.length-3].$el;
        }

        //once the animation is done, reset to original state and execute callback parameter
        $bottomDrawer.one('webkitTransitionEnd oTransitionEnd otransitionend transitionend msTransitionEnd', _.bind(function(){
            this._removeTabBehavior($topDrawer); //remove expand/collapse tab event handlers
            $bottomDrawer.find('.drawer-tab').remove(); //remove drawer tab
            $bottomDrawer.find('.drawer-backdrop').remove(); //remove backdrop
            if (this._components.length === 1) {
                $bottomDrawer.removeClass('drawer');
            }
            callback();
        }, this));

        //start the animation to close the drawer
        $topDrawer.css('top', $topDrawer.offset().top-drawerHeight);
        $bottomDrawer.css('top','');
        if ($hiddenDrawer) {
            $hiddenDrawer.css('top', this._components.length === 2 ? drawerHeight : $hiddenDrawer.offset().top - drawerHeight);
        }
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

        return windowHeight - headerHeight - footerHeight - 64; //64px above the footer
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
     * Add the ability to expand and collapse the drawer when the tab is clicked
     * @param $top
     * @param $bottom
     * @private
     */
    _handleTabBehavior: function($top, $bottom) {
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
     * Remove the event listener that handles the ability to expand and collapse the drawer
     * @param $drawer
     * @private
     */
    _removeTabBehavior: function($drawer) {
        $drawer.find('.drawer-tab').off('click');
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
    }
})