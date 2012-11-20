({
    extendsFrom: 'button',

    events: {
        'click a': 'toggle'
    },

    toggle: function() {
        var chevron = this.$('.drawerTrig span'),
            pointRightClass = 'icon-chevron-right',
            pointLeftClass = 'icon-chevron-left';

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