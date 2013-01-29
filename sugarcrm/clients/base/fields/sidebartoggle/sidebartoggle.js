({
    extendsFrom: 'button',

    events: {
        'click a': 'toggle'
    },

    toggle: function() {
        var chevron = this.$('.drawerTrig span'),
            pointRightClass = 'icon-double-angle-right',
            pointLeftClass = 'icon-double-angle-left';

        if (chevron.hasClass(pointRightClass)) {
            chevron
                .removeClass(pointRightClass)
                .addClass(pointLeftClass);
        } else {
            chevron
                .removeClass(pointLeftClass)
                .addClass(pointRightClass);
        }

        this.context.trigger('toggleSidebar');
    }
})