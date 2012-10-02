({
    extendsFrom:'ListView',

    /**
     * @private
     * @var _previewEls
     */
    _previewEls: [],
    
    gTable: '',

    events: {
        "click .action-edit": "edit",
        "click .action-preview": "preview"
    },
    
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("quickcreate:list:toggle", null, this);
        this.context.on("quickcreate:list:toggle", this.toggleList, this);
        this.context.on("quickcreate:list:close", this.close, this);
        
        _.bindAll(this, "closePreviews");
    },

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    _renderHtml: function() {
        this.limit = this.context.get('limit') || 2;
        app.view.View.prototype._renderHtml.call(this);

        this.gTable = this.$('.quickcreateListTable').dataTable({
            "bPaginate": false,
            "bFilter": false,
            "bInfo": false
        });

        this.$('.dataTables_filter').on("scroll", this.closePreviews);
    },
    
    close: function() {
        this.destroyPreviews();
        $('body').off("click", this.closePreviews);
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
        var $button = $(e.target).closest('.action-preview');
        
        if (_.isUndefined($button.data("popover"))) {
            this.buildPreview($button);
        }
        
        $button.popover("toggle");
    },

    /**
     * Build the preview popover
     */
    buildPreview: function($button) {
        var $parentRow = $button.closest("tr"),
            recordId = $parentRow.data("record-id"),
            model = this.collection.get(recordId);
        
        // Create a custom context so that the view can access field data.
        var context = app.context.getContext({
            model: model
        });
        
        // Fake metadata to to emulate a detail view of only the fields available on
        // the quickcreate form.
        var meta = _.extend({}, app.metadata.getView(this.module, "quickcreate"), { type: "detail" });
        
        // Remove metadata for all fields that don't have a value.
        var fields = _.first(meta.panels).fields;
        var compressedFields = [];
        
        _.first(meta.panels).fields = compressedFields;
        _.each(fields, function(field, index) {
            var fieldValue = model.get(field.name);
            if ( !_.isUndefined(fieldValue) && !_.isEmpty(fieldValue) ) {
                compressedFields.push(field);
            }
        });
        
        // Create a view that we'll manually "render" into the popover.
        var view = app.view.createView({
            context: context,
            module: this.module,
            name: "property-table",
            meta: meta
        });
        
        view.render();
        
        $button.popover({
            "title": model.get("name"),
            "content": view.$el.html(),
            "trigger": "manual"
        });

        view._dispose();
        
        $button.data("popover").tip().addClass("no-padding");

        // Save these for later.
        this._previewEls.push($button);
    },

    /**
     * Hides all open previews when called manually.  If called as an event handler, analyzes the
     * event object to ensure that only the appropriate popovers are closed.
     * 
     * @param e Event object, if this was triggered by an event.
     */
    closePreviews: function(e) {
        var target           = e ? e.target : null;
        var $target          = $(target);
        var wasInsidePopover = !!$target.closest(".popover").length;

        if ( !wasInsidePopover ) {
            _.each(this._previewEls, function($el) {
                var popoverData = $el.data('popover');
                // Ensure that we're not closing the the popover for the element that was clicked.
                // Closes if there is no target (i.e., nothing was clicked)
                // Closes if the target is not an element that is bound to a popover instance.
                // Closes if the click did not occur on or inside the element that triggered the popover.
                if ( target === null || _.isUndefined(popoverData) || $target.closest(popoverData.$element).length === 0  ) {
                    $el.popover('hide');
                }
            });
        }
    },
    
    /**
     * Destroys all popovers.
     */
    destroyPreviews: function() {
        _.each(this._previewEls, function($el) {
            $el.data('popover').tip().remove();
        });
        this._previewEls = [];
    },

    /**
     * Either show or hide the list table
     * @param show
     */
    toggleList: function(show) {
        var table = this.$('.dataTables_filter');
        if (show) {
            table.show();
            $('body').on("click", this.closePreviews);
        } else {
            table.hide();
            $('body').off("click", this.closePreviews);
        }
    }
})

