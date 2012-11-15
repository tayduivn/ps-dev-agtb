({
    events: {
        'click [rel=tooltip]': 'fixTooltip',
        'click .search': 'showSearch',
        'click .relate': 'showCreate'
    },

    _renderHtml: function() {
        if (app.acl.hasAccess('create', this.module)) {
            this.context.set('isCreateEnabled', true);
        }

        app.view.View.prototype._renderHtml.call(this);
    },

    fixTooltip: function() {
        this.$(".tooltip").hide();
    },

    showSearch: function() {
        // Toggle on search filter and off the pagination buttons
        this.$('.search').toggleClass('active');
        this.layout.trigger("list:search:toggle");
    },

    showCreate: function(e) {
        // Check to see if current module and global context module align
        if (this.collection.module == app.controller.context.get("module")) {

        } else {
            e.preventDefault();

            console.log("Show Relate");
            this.$('.relate-bar').slideToggle('fast');
        }
    },

    relate: function() {

    }
})
