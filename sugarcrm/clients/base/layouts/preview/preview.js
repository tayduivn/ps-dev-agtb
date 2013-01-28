({
    events: {
        'click [data-direction]': 'triggerPagination',
        'click .preview-headerbar .closeSubdetail': 'triggerClose'
    },

    initialize: function(opts) {
        _.bindAll(this);

        this.template = app.template.get("l.preview");
        this.renderHtml();
        app.view.Layout.prototype.initialize.call(this, opts);
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
        this.$el.addClass("preview-pane");
    },

    triggerPagination: function(e) {
        this.trigger("preview:pagination:fire", this.$(e.currentTarget).data());
    },

    triggerClose: function() {
        this.context.trigger("list:preview:decorate", null, this);
        this.context.trigger("preview:close:fire");
    }
})