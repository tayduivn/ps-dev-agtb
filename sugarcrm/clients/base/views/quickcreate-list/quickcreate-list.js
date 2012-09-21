({
    extendsFrom:'ListView',
    
    events: {
        "click .action-edit": "edit"
    },
    
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("quickcreate:list:toggle", null, this);
        this.context.on("quickcreate:list:toggle", this.toggleList, this);

        this.context.on("quickcreate:list:close", this.close, this);
    },

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    _renderHtml: function() {
        this.limit = this.context.get('limit') ? this.context.get('limit') : 2;
        app.view.View.prototype._renderHtml.call(this);
    },
    
    close: function() {
        this.$('.dataTables_filter').hide();
    },
    
    edit: function(e) {
        var $button = $(e.target);
        var $parentRow = $button.closest("tr");
        var recordId = $parentRow.data("record-id");
        var editModel = this.collection.get(recordId);
        this.context.trigger('quickcreate:edit', editModel);
        this.context.trigger('quickcreate:list:close');
    },

    /**
     * Either show or hide the list table
     * @param show
     */
    toggleList: function(show) {
        var table = this.$('.dataTables_filter');
        if (show) {
            table.show();
        } else {
            table.hide();
        }
    }
})

