({
    extendsFrom: 'button',

    events: {
        'click .drawerTrig': 'toggle'//ensure "hit area" big enough
    },
    _render: function() {
        app.view.Field.prototype._render.call(this);
        // Broadcast when we've fully rendered sidebar toggle
        app.controller.context.trigger("sidebartoggle:rendered");
    },
    bindDataChange:function () {
        // These corresponding to the toggleSide & openSide events in default layout
        app.controller.context.on("toggleSidebarArrows", this.updateArrows, this);
        app.controller.context.on("sidebarArrowsOpen", this.sidebarArrowsOpen, this);
    },
    updateArrows: function() {
        var chevron = this.$('.drawerTrig i'),
            pointRightClass = 'icon-double-angle-right';
        if (chevron.hasClass(pointRightClass)) {
            this.updateArrowsWithDirection('close');
        } else {
            this.updateArrowsWithDirection('open');
        }
    },
    sidebarArrowsOpen: function() {
        this.updateArrowsWithDirection('open');
    },
    updateArrowsWithDirection: function(state) {
        var chevron = this.$('.drawerTrig i'),
            pointRightClass = 'icon-double-angle-right',
            pointLeftClass = 'icon-double-angle-left';
        if (state === 'open') {
            chevron.removeClass(pointLeftClass).addClass(pointRightClass);
        } else if (state === 'close') {
            chevron.removeClass(pointRightClass).addClass(pointLeftClass);
        } else {
            app.logger.warn("updateArrowsWithDirection called with invalid state; should be 'open' or 'close', but was: "+state)
        }
    },
    // If toggled from a user clicking on anchor simply trigger toggleSidebar
    toggle: function() {
        this.context.trigger('toggleSidebar');
    }
})
