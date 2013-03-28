({
    events: {
        "change input": "toggleDisabled",
        "click a.filter-close": "triggerClose",
        "click a.save_button:not(.disabled)": "triggerSave",
        "click a.delete_button:not(.hide)": "triggerDelete"
    },

    tagName: "article",
    className: "filter-header",
    rowState: false,

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        this.layout.on("filter:create:open", function(model) {
            var self = this,
                name = model ? model.get("name") : '';
            this.setFilterName(name);
            if (!name) {
                // We are creating a new filter.
                _.defer(function() {
                    self.$("input").focus();
                });
            }
        }, this);

        this.listenTo(this.layout, "filter:create:rowsValid", this.toggleRowState);
        this.listenTo(this.layout, "filter:set:name", this.setFilterName);
    },

    getFilterName: function() {
        return this.$("input").val();
    },

    setFilterName: function(name) {
        this.$("input").val(name);
        // We have this.layout.editingFilter if we're setting the name.
        this.toggleDelete(!name);
    },

    toggleDelete: function(t) {
        this.$(".delete_button").toggleClass("hide", t);
    },

    toggleDisabled: function() {
        this.$(".save_button").toggleClass('disabled', !(this.getFilterName() && this.rowState));
    },

    toggleRowState: function(t) {
        this.rowState = _.isUndefined(t) ? !this.rowState : !!t;
        this.toggleDisabled();
    },

    triggerClose: function() {
        var id = this.layout.editingFilter.get('id');
        this.layout.trigger("filter:create:close", true, id);
    },

    triggerSave: function() {
        var filterName = this.getFilterName();
        this.layout.trigger("filter:create:save", filterName);
    },

    triggerDelete: function() {
        this.layout.trigger("filter:create:delete");
    }
})
