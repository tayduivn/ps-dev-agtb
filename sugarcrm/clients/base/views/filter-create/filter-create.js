({
    events: {
        'click a.filter-close': 'triggerClose',
        'click a.addme': 'addRow'
    },

    initialize: function(opts) {
        var self = this;
        this.title = app.controller.context.get('module');
        app.view.View.prototype.initialize.call(this, opts);
    },

    render: function() {
        app.view.View.prototype.render.call(this);
        this.fields = app.metadata.getModule(this.title).fields;
        _.each(this.fields, function(value, key) {
            var el = $("<option />").attr('value', key).text(app.lang.getAppString(value.vname));
            self.$('#filter_row_new select.field_name').append(el);
        });
    },

    addRow: function(e) {
    },

    triggerClose: function() {
        this.layout.trigger("filter:create:close:fire");
    }
})
