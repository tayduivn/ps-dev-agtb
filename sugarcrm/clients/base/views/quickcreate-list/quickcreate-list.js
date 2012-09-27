({
    extendsFrom:'ListView',
    gTable:'',

    events: {
        "click .action-edit": "edit",
        "click .action-preview": "preview"
    },
    
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("quickcreate:list:toggle", null, this);
        this.context.on("quickcreate:list:toggle", this.toggleList, this);
        this.context.on("quickcreate:list:close", this.close, this);
    },

    /**
     * Renders view
     */
    _render: function() {
        var self = this;
        app.view.View.prototype._render.call(this);

        this.gTable = this.$('.quickcreateListTable').dataTable(
            {
                "bPaginate": false,
                "bFilter": false,
                "bInfo": false
            }
        );

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

    /**
     * Handle selecting a record to edit
     * @param e
     */
    edit: function(e) {
        var $button = $(e.target),
            $parentRow = $button.closest("tr"),
            recordId = $parentRow.data("record-id"),
            editModel = this.collection.get(recordId);

        this.context.trigger('quickcreate:edit', editModel);
        this.context.trigger('quickcreate:alert:dismiss');
        this.context.trigger('quickcreate:list:close');
        this.context.trigger('quickcreate:clearHighlightDuplicateFields');
    },

    /**
     * Handle selecting a record to preview
     * @param e
     */
    preview: function(e) {
        var $button = $(e.target);

        if (_.isUndefined($button.data("popover"))) {
            this.buildPreview($button);
        }

        $button.popover("toggle");
    },

    /**
     * Build the preview popover
     */
    buildPreview: function($button) {
        console.log('building preview');
        var $parentRow = $button.closest("tr"),
            recordId = $parentRow.data("record-id"),
            model = this.collection.get(recordId);

        $button.popover({
            "title": model.get("name"),
            "content": "test",
            "trigger": "manual"
        });
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

