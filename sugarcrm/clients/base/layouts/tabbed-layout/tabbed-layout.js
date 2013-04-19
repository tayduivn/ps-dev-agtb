({
    initialize: function(options) {
        this.firstIsActive = false;
        this.template = app.template.get("l.tabbed-layout");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, options);
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
    },

    // Assign the tabs
    _placeComponent: function(comp, def) {
        var id = _.uniqueId('record-bottom'),
            nav = $('<li/>').html('<a href="#' + id + '" onclick="return false;" data-toggle="tab">' + app.lang.get(def.layout.name, this.module) + '</a>'),
            content = $('<div/>').addClass('tab-pane').attr('id', id).html(comp.el);

        if (!this.firstIsActive) {
            nav.addClass('active');
            content.addClass('active');
        }

        this.firstIsActive = true;
        this.$('.tab-content').append(content);
        this.$('.nav').append(nav);
    }
})
